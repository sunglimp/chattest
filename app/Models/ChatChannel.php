<?php

namespace App\Models;

use App\Http\Controllers\Api\V1\AgentController;
use App\Models\ChatMessagesHistory;
use App\Models\Group;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Events\ChatTerminateByForce;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use App\Events\ChatTerminate;
use App\Jobs\SendChatToMlModel;
use App\Jobs\ChatAutoTransfer;
use Illuminate\Support\Facades\Redis;
use Cache;
use App\Repositories\ExpireChatRepository;
use App\Agent;


/**
 * @author Ankit Vishwakarma <ankit.vishwakarma@vfirst.com>
 * @uses App\Observers\ChatChannelObserver
 */
class ChatChannel extends Model
{

    const CHANNEL_PREFIX                       = 'visitor-';
    const CHANNEL_STATUS_UNPICKED              = 1;
    const CHANNEL_STATUS_PICKED                = 2;
    const CHANNEL_STATUS_TRANSFERED            = 3;
    const CHANNEL_STATUS_TERMINATED_BY_AGENT   = 4;
    const CHANNEL_STATUS_TERMINATED_BY_VISITOR = 5;
    const CHAT_STATUS_DISCARD                  = 0;
    const CHAT_STATUS_PENDING                  = 1;
    const CHAT_STATUS_ACCEPT                   = 2;
    const CHAT_ONLINE_USERS                    = 1;
    const CHAT_CLOSED_VIA_VISITOR_TIMEOUT      = 2;
    const CHAT_CLOSED_VIA_VISITOR_LEFT         = 3;
    const CHAT_CLOSED_VIA_AGENT_FORCE_LOGOUT   = 4;
    const CHAT_CLOSED_VIA_AGENT_CLOSE          = 5;

    protected $dateFormat        = 'U';
    public $timestamps           = true;
    protected $fillable          = ['channel_id', 'channel_name', 'agent_id', 'group_id', 'client_id', 'parent_id', 'root_channel_id', 'queued_at', 'agent_assigned_at', 'via_internal_transfer', 'end_point', 'token', 'source_type'];
    protected static $terminated = [
        self::CHANNEL_STATUS_TERMINATED_BY_AGENT,
        self::CHANNEL_STATUS_TERMINATED_BY_VISITOR,
        self::CHANNEL_STATUS_TRANSFERED
    ];

    public function scopeActiveSubscribers($query)
    {
        return $query->whereNotIn(
                        'status', [
                    self::CHANNEL_STATUS_TERMINATED_BY_AGENT,
                    self::CHANNEL_STATUS_TERMINATED_BY_VISITOR,
                    self::CHANNEL_STATUS_TRANSFERED
                        ]
        );
    }

    public function scopeOfAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeUnpicked($query)
    {
        return $query->whereNull('agent_id')->where('status', self::CHANNEL_STATUS_UNPICKED);
    }

    public function scopeWaitingQueue($query, $take = 1)
    {
        return $query
                        ->unpicked()
                        ->oldest()
                        ->take($take);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Function to pick chat.
     *
     * @param integer $chatId
     * @param integer $agentId
     */
    public static function pickChat($agentId, $chatId)
    {
        try {

            $currentTime = Carbon::now()->timestamp;
            $isUpdated   = self::where('agent_id', $agentId)
                    ->where('id', $chatId)
                    ->where('status', self::CHANNEL_STATUS_UNPICKED)
                    ->update(['status' => config('constants.CHAT_STATUS.PICKED'), 'accepted_at' => $currentTime, 'updated_at' => $currentTime]);

            //First time pick
            ChatMessagesHistory::where('chat_channel_id', $chatId)->update(['read_at' => $currentTime, 'updated_at' => $currentTime]);
            return $isUpdated;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to close chat.
     *
     * @param integer $chatId
     * @param integer $agentId
     * @deprecated use public function close instead
     */
    public static function closeChat($agentId, $chatId, $status = null)
    {
        $status      = $status ?? config('constants.CHAT_STATUS.TERMINATED_BY_AGENT');
        $currentTime = Carbon::now()->timestamp;
        try {
            $chat      = self::where('agent_id', $agentId)->where('id', $chatId)->first();
            $isUpdated = false;
            if ($chat->status == self::CHANNEL_STATUS_UNPICKED || $chat->status == self::CHANNEL_STATUS_PICKED) {
                $chat->closed_via    = self::CHAT_CLOSED_VIA_AGENT_CLOSE;
                $chat->status        = $status;
                $chat->terminated_at = $currentTime;
                $isUpdated           = true;
                $chat->save();
                (new ExpireChatRepository)->destroyChatKey($chatId);
                event(new ChatTerminate($agentId, $chatId, $chat->via_internal_transfer));
            }
            return $isUpdated;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function close($status = null, $isSessionTimeout = null)
    {
        info("in chatClose function" . $status);
        $status      = $status ?? config('constants.CHAT_STATUS.TERMINATED_BY_AGENT');
        $currentTime = Carbon::now()->timestamp;
        $isUpdated   = false;
        if (in_array($this->status, [self::CHANNEL_STATUS_UNPICKED, self::CHANNEL_STATUS_PICKED])) {
            info("=======Status is ::" . $status);

            // Condition for closed_via column in visitor case
            if($status==self::CHANNEL_STATUS_TERMINATED_BY_VISITOR && isset($isSessionTimeout))
            {
                $this->closed_via = $isSessionTimeout ? self::CHAT_CLOSED_VIA_VISITOR_TIMEOUT : self::CHAT_CLOSED_VIA_VISITOR_LEFT;
            }

            $this->status        = $status;
            $this->terminated_at = $currentTime;
            $isUpdated           = true;
            $this->save();
            (new ExpireChatRepository)->destroyChatKey($this->id);
            if ($this->agent_id != null) {
                event(new ChatTerminate($this->agent_id, $this->id, $this->via_internal_transfer, $isSessionTimeout));
            }
        }
        return $isUpdated;
    }

    /**
     * Function to check whether active chats are available.
     *
     * @param  integer $agentId
     * @throws \Exception
     */
    public static function checkChatAvailable($agentId)
    {
        try {
            return self::where('agent_id', $agentId)->activeSubscribers()->get();
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function scopeTerminated($query)
    {
        return $query->whereNotNull('terminated_at');
    }

    public function scopeTerminatedByAgent($query)
    {
        return $query->terminated()->where('chat_channels.status', self::CHANNEL_STATUS_TERMINATED_BY_AGENT);
    }

    public function scopeTerminatedByVisitor($query)
    {
        return $query->terminated()->where('chat_channels.status', self::CHANNEL_STATUS_TERMINATED_BY_VISITOR);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Function to get number of chats.
     *
     * @param  string $now in format 'Y-m-d'
     * @param array $agents
     * @throws \Exception
     *
     */
    public static function getChatCountOnDate($now, $agents = [])
    {
        try {
            is_valid_date($now);
            $timeStamps = get_start_end_timestamps($now);
            self::select('organization_id', 'agent_id', DB::raw('COUNT(chat_channels.id) as count_chat'))
                    ->join('users', 'users.id', '=', 'chat_channels.agent_id')
                    ->where(function($query) use($timeStamps){
                        $query->where('chat_channels.updated_at', '>=', $timeStamps[0]);
                        $query->where('chat_channels.updated_at', '<', $timeStamps[1]);
                    })
                    ->groupBy('agent_id')
                    ->whereNull('transferred_at')
                    ->whereIn('agent_id', $agents)
                    ->get()
                    ->summarize($now, 'count_chat');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to make chat terminated in case of active chats.
     *
     * @param  integer $agentId
     * @throws \Exception
     */
    public static function makeChatTerminated($agentId)
    {
        try {
            $chats       = self::where('agent_id', $agentId)->activeSubscribers()->where('status', '!=', self::CHANNEL_STATUS_UNPICKED)->get();
            $currentTime = Carbon::now()->timestamp;
            foreach ($chats as $chat) {
                event(new ChatTerminateByForce($agentId, $chat->id));
                if ($chat->source_type == 'whatsapp') {
                    event(new \App\Events\ChatTerminateWhatsapp($chat->agent_id, $chat->id));
                }
               (new ExpireChatRepository)->destroyChatKey($chat->id);
            }
            $query        = self::where('agent_id', $agentId)->where('status', '!=', self::CHANNEL_STATUS_UNPICKED)->activeSubscribers();
            $query->where('agent_id', $agentId)->activeSubscribers()
                    ->update(['status' => self::CHANNEL_STATUS_TERMINATED_BY_AGENT, 'closed_via'=> self::CHAT_CLOSED_VIA_AGENT_FORCE_LOGOUT, 'terminated_at' => $currentTime, 'updated_at' => $currentTime]);
            $chatChannels = self::where('agent_id', $agentId)->where('status', '=', self::CHANNEL_STATUS_UNPICKED)->get();
            foreach ($chatChannels as $chatChannel) {

                $availableUsers = Redis::smembers('group_' . $chatChannel->group_id);
                //$onlineUsers = array_diff($availableUsers, [Auth::user()->id] );
                $onlineUsers    = array_diff($availableUsers, [$agentId]);
                if (empty($onlineUsers) || count($onlineUsers) < self::CHAT_ONLINE_USERS) {
                    if ($chatChannel->source_type == 'whatsapp') {
                        event(new \App\Events\ChatTerminateWhatsapp($chatChannel->agent_id, $chatChannel->id, 1));
                    } else {
                        event(new ChatTerminateByForce($chatChannel->agent_id, $chatChannel->id, 1));
                    }
                } else {
                    ChatAutoTransfer::dispatch($chatChannel, 1)
                            ->onQueue(config('chat.queues.auto_transfer'));
                }
            }
            $unpickedChats = self::where('agent_id', $agentId)->where('status', '=', self::CHANNEL_STATUS_UNPICKED)->activeSubscribers();
            if (empty($onlineUsers) || count($onlineUsers) < self::CHAT_ONLINE_USERS) {
                $unpickedChats->update(['status' => self::CHANNEL_STATUS_TERMINATED_BY_AGENT, 'closed_via'=> self::CHAT_CLOSED_VIA_AGENT_FORCE_LOGOUT, 'terminated_at' => $currentTime, 'updated_at' => $currentTime]);
            }
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get average session.
     *
     *
     * @param  string $now
     * @param array $agents
     * @throws \Exception
     */
    public static function getAverageSession($now, $agents = [])
    {
        try {
            is_valid_date($now);

            User::select(
                            'users.id  as agent_id', 'users.organization_id', 'chat_channels.group_id', DB::raw(
                                    'IF(chat_channels.transferred_at IS NOT NULL,
                    AVG(chat_channels.transferred_at - chat_channels.accepted_at),
                    AVG(chat_channels.terminated_at - chat_channels.accepted_at))
                    as avg_session'
                            )
                    )->join('chat_channels', function ($query) use ($now) {
                        $query->on('chat_channels.agent_id', '=', 'users.id')
                        ->where(DB::raw('DATE(FROM_UNIXTIME(chat_channels.updated_at))'), $now)
                        ->whereNotNUll('accepted_at');
                    })
                    ->whereNotIn('users.role_id', config('config.ADMIN_ROLE_IDS'))
                    ->whereIn('users.id', $agents)
                    ->withTrashed() //Already filtering, this will speed up query
                    ->groupBy('users.id')
                    ->get()
                    ->summarize($now, 'avg_session');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get resolved chats.
     *
     * @param  string $now
     * @param array $agents
     * @throws \Exception
     */
    public static function getChatsResolved($now, $agents = [])
    {
        try {
            is_valid_date($now);
            $timeStamps = get_start_end_timestamps($now);
            self::select('organization_id', 'agent_id', DB::raw('COUNT(chat_channels.id) as count_chat_resolved'))
                    ->join('users', 'users.id', '=', 'chat_channels.agent_id')
                    ->where(function($query) use($timeStamps){
                        $query->where('chat_channels.updated_at', '>=', $timeStamps[0]);
                        $query->where('chat_channels.updated_at', '<', $timeStamps[1]);
                    })
                    ->where('chat_channels.status', self::CHANNEL_STATUS_TERMINATED_BY_AGENT)
                    ->whereNotNull('chat_channels.accepted_at')
                    ->whereIn('agent_id', $agents)
                    ->groupBy('chat_channels.agent_id')
                    ->get()
                    ->summarize($now, 'count_chat_resolved');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get average chat.
     *
     * @param  string $now
     * @param array $agents
     * @throws \Exception
     *
     */
    public static function getAverageChat($now, $agents)
    {
        try {
            is_valid_date($now);
            User::select(
                            'users.id as agent_id', 'users.organization_id', 'chat_channels.group_id', DB::raw('count(chat_channels.id) as avg_chat')
                    )->join('chat_channels', function ($query) use ($now) {
                        $query->on('chat_channels.agent_id', '=', 'users.id')
                        ->where(DB::raw('DATE(FROM_UNIXTIME(chat_channels.created_at))'), $now);
                    })
                    ->whereNotIn('users.role_id', config('config.ADMIN_ROLE_IDS'))
                    ->whereIn('users.id', $agents)
                    ->withTrashed() // We alreday checked deleted agents so this will reduce query execution time
                    ->groupBy('users.id')
                    ->get()
                    ->summarize($now, 'avg_chat');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get count of chats transferred.
     *
     * @param  string $now
     * @param array $agents
     * @throws \Exception
     */
    public static function getChatCountTransferred($now, $agents = [])
    {
        try {
            is_valid_date($now);
            $timeStamps = get_start_end_timestamps($now);
            self::select(['agent_id', \DB::raw('COUNT(*) AS count_chat_transferred'), 'organization_id'])
                    ->join('users', 'users.id', '=', 'chat_channels.agent_id')
                    ->where(function($query) use($timeStamps){
                        $query->where('chat_channels.transferred_at', '>=', $timeStamps[0]);
                        $query->where('chat_channels.transferred_at', '<', $timeStamps[1]);
                    })
                    ->whereNotNull('transferred_at')
                    ->whereIn('agent_id', $agents)
                    ->groupBy('agent_id')
                    ->get()
                    ->summarize($now, 'count_chat_transferred');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * FUnction to get count of In-session missed chats.
     *
     * @param  string $now
     * @param array $agents
     * @throws \Exception
     */
    public static function getMissedChats($now, $agents = [])
    {
        try {
            is_valid_date($now);
            $timeStamps = get_start_end_timestamps($now);
            self::select('organization_id', 'agent_id', \DB::raw('COUNT(*) AS count_chat_missed'))
                    ->join('users', 'users.id', '=', 'chat_channels.agent_id')
                    ->where(function($query) use($timeStamps){
                        $query->where('chat_channels.updated_at', '>=', $timeStamps[0]);
                        $query->where('chat_channels.updated_at', '<', $timeStamps[1]);
                    })
                    ->whereIn('agent_id', $agents)
                    ->whereNull('accepted_at')
                    ->whereIn('closed_via', [self::CHAT_CLOSED_VIA_VISITOR_LEFT, self::CHAT_CLOSED_VIA_AGENT_FORCE_LOGOUT, self::CHAT_CLOSED_VIA_AGENT_CLOSE])
                    ->groupBy('agent_id')
                    ->get()
                    ->summarize($now, 'count_chat_missed');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * FUnction to get count of Out-session missed chats.
     *
     * @param  string $now
     * @param array $agents
     * @throws \Exception
     */
    public static function getOutSessionMissedChats($now)
    {
        try {
            is_valid_date($now);
            $timeStamps = get_start_end_timestamps($now);
            self::select('organization_id', 'agent_id', \DB::raw('COUNT(*) AS count_chat_missed'))
                    ->join('groups', 'groups.id', '=', 'chat_channels.group_id')
                    ->where(function($query) use($timeStamps){
                        $query->where('chat_channels.updated_at', '>=', $timeStamps[0]);
                        $query->where('chat_channels.updated_at', '<', $timeStamps[1]);
                    })
                    ->whereNull('agent_id')
                    ->whereNotNull('queued_at')
                    ->where('closed_via', self::CHAT_CLOSED_VIA_VISITOR_LEFT)
                    ->groupBy('organization_id')
                    ->get()
                    ->summarize($now, 'count_chat_missed');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    /**
     * Function to get Queued Chats.
     *
     * @param  string $now
     * @param array $agents
     * @throws \Exception
     */
    public static function getEnteredChat($now, $agents = [])
    {
        try {
            is_valid_date($now);

            self::select('organization_id', 'agent_id', \DB::raw('COUNT(chat_channels.id) AS count_entered_chat'), 'chat_channels.group_id')
                    ->join('groups', 'groups.id', '=', 'chat_channels.group_id')
                    ->where(\DB::raw('DATE(FROM_UNIXTIME(agent_assigned_at))'), $now)
                    ->whereIn('agent_id', $agents)
                    ->whereNotNull('agent_id')
                    ->whereNotNull('agent_assigned_at')
                    ->whereNotNull('queued_at')
                    ->groupBy('organization_id', 'agent_id')
                    ->get()
                    ->summarize($now, 'count_entered_chat');
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            die;
            throw $exception;
        }
    }

    /**
     * Function to get entered chats.
     *
     * @param  string $now
     * @throws \Exception
     */
    public static function getQueuedVisitor($now)
    {
        try {
            is_valid_date($now);

            self::select('organization_id', 'agent_id', \DB::raw('COUNT(chat_channels.id) AS count_queued_visitor'), 'chat_channels.group_id')
                    ->join('groups', 'groups.id', '=', 'chat_channels.group_id')
                    ->where(\DB::raw('DATE(FROM_UNIXTIME(queued_at))'), $now)
                    ->whereNull('agent_id')
                    ->whereNotNull('queued_at')
                    ->groupBy('organization_id', 'agent_id')
                    ->get()
                    ->summarize($now, 'count_queued_visitor');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get "Out Session" Timeout
     *
     * @param  string $now
     * @throws \Exception
     */
    public static function getQueuedLeftChats($now)
    {
        try {
            is_valid_date($now);
            $timeStamps = get_start_end_timestamps($now);
            self::select('organization_id', 'agent_id', \DB::raw('COUNT(chat_channels.id) AS count_queued_left'), 'chat_channels.group_id')
                    ->join('groups', 'groups.id', '=', 'chat_channels.group_id')
                    ->where(function($query) use($timeStamps){
                        $query->where('chat_channels.updated_at', '>=', $timeStamps[0]);
                        $query->where('chat_channels.updated_at', '<', $timeStamps[1]);
                    })
                    ->whereNull('agent_id')
                    ->whereNotNull('queued_at')
                    ->where('chat_channels.closed_via', self::CHAT_CLOSED_VIA_VISITOR_TIMEOUT)
                    ->groupBy('organization_id')
                    ->get()
                    ->summarize($now, 'count_queued_left');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public static function getCountInSessionTimeouts($now, $agents = [])
    {
        try {
            is_valid_date($now);
            $timeStamps = get_start_end_timestamps($now);
            self::select('organization_id', 'agent_id', \DB::raw('COUNT(*) AS count_insession_timeout'))
                    ->join('users', 'users.id', '=', 'chat_channels.agent_id')
                    ->where(function($query) use($timeStamps){
                        $query->where('chat_channels.updated_at', '>=', $timeStamps[0]);
                        $query->where('chat_channels.updated_at', '<', $timeStamps[1]);
                    })
                    ->whereIn('agent_id', $agents)
                    ->whereNull('accepted_at')
                    ->where('closed_via', self::CHAT_CLOSED_VIA_VISITOR_TIMEOUT)
                    ->groupBy('agent_id')
                    ->get()
                    ->summarize($now, 'count_insession_timeout');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function isTerminated()
    {
        return in_array($this->status, self::$terminated);
    }

    public function isAccepted()
    {
        return $this->status == self::CHANNEL_STATUS_PICKED;
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function historyMessage()
    {
        return $this->hasMany(ChatMessagesHistory::class, 'chat_channel_id', 'id');
    }

    public function chatMessage()
    {
        return $this->historyMessage();
    }

    public function chatChannelResponseTiming()
    {
        return $this->hasOne(ChatChannelResponseTiming::class);
    }

    public function scopeClosedChannels($query)
    {
        return $query->whereIn('status', self::$terminated);
    }

    /**
     * Function to get chat channels by agentId.
     */
    public static function getChannels($agentId, $isInternal = false, $agentIds = [])
    {
        try {
            $channelType = $isInternal ? 'internal_comment' : 'basic';
            $query       = ChatChannel::select('chat_channels.id', 'channel_name', 'client_id', 'agent_id', 'group_id', 'parent_id', 'root_channel_id', 'raw_info', DB::raw("'$channelType' as channel_type"), 'identifier as client_display_name', DB::raw('IF(clients.updated_at > clients.created_at,1,0) as history_status'), 'chat_channels.status', 'users.name', 'users.role_id', 'roles.name as role_name', 'chat_channels.source_type')
                    ->join('clients', 'chat_channels.client_id', '=', 'clients.id')
                    ->join('users', 'chat_channels.agent_id', '=', 'users.id')
                    ->join('roles', 'users.role_id', '=', 'roles.id');
            if (!empty($agentIds)) {
                $query->whereIn('agent_id', $agentIds);
            } elseif ($isInternal == true) {
                $channelIds = InternalCommentChannel::getChannelIdsByAgentId($agentId);
                $query->WhereIn('chat_channels.id', $channelIds);
            } else {
                $query->where('agent_id', $agentId);
            }
            $chatChannels = $query->whereIn('chat_channels.status', [self::CHANNEL_STATUS_UNPICKED, self::CHANNEL_STATUS_PICKED])
                    ->orderby('chat_channels.created_at', 'DESC')
                    ->get();

            return $chatChannels;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get chats closed by visitor i.e.
     visitor left from accepted list or visitor timeout from accepted list
     *
     * @param  string $now
     * @param array $agents
     * @throws Exception
     */
    public static function getChatsTerminatedByVisitor($now, $agents)
    {
        try {
            is_valid_date($now);
            $timeStamps = get_start_end_timestamps($now);
            self::select(['organization_id', 'agent_id', \DB::raw('COUNT(*) AS count_chat_terminated_by_visitor'), 'group_id'])
                    ->join('users', 'users.id', '=', 'chat_channels.agent_id')
                    ->where(function($query) use($timeStamps){
                        $query->where('chat_channels.updated_at', '>=', $timeStamps[0]);
                        $query->where('chat_channels.updated_at', '<', $timeStamps[1]);
                    })
                    ->where('chat_channels.status', self::CHANNEL_STATUS_TERMINATED_BY_VISITOR)
                    ->whereIn('agent_id', $agents)
                    ->whereNotNull('accepted_at')
                    ->groupBy('agent_id')
                    ->get()
                    ->summarize($now, 'count_chat_terminated_by_visitor');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get chats terminated by agennt.
     *
     * @param  string $now
     * @param array $agents
     * @throws Exception
     */
    public static function getChatsTerminatedByAgent($now, $agents = [])
    {
        try {
            is_valid_date($now);

            self::select(['organization_id', 'agent_id', \DB::raw('COUNT(*) AS count_chat_terminated_by_agent')])
                    ->terminatedByAgent()
                    ->join('users', 'users.id', '=', 'chat_channels.agent_id')
                    ->where(\DB::raw('DATE(FROM_UNIXTIME(terminated_at))'), $now)
                    ->whereIn('agent_id', $agents)
                    ->groupBy('organization_id', 'agent_id')
                    ->get()
                    ->summarize($now, 'count_chat_terminated_by_agent');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public static function getActiveChannel($channelName)
    {

        return self::where('channel_name', $channelName)->latest()->first();
    }

    /**
     * Function to get active channelid by channel name.
     *
     * @param string $channelName
     * @throws Exception
     */
    public static function getActiveChannelId($channelName)
    {
        try {
            $chatId = ChatChannel::where('channel_name', $channelName)->activeSubscribers()->first();
            if (!empty($chatId)) {
                return $chatId->id;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get feedback.
     *
     * @param string $now
     * @param array $agents
     *
     * @throws \Exception
     */
    public static function getFeedBack($now, $agents)
    {
        try {
            User::select(
                            'users.id as agent_id', 'users.organization_id', 'chat_channels.group_id', DB::raw('AVG(feedback) AS avg_feedback')
                    )->join('chat_channels', function ($query) use ($now) {
                        $query->on('chat_channels.agent_id', '=', 'users.id')
                        ->where(DB::raw('DATE(FROM_UNIXTIME(chat_channels.updated_at))'), $now);
                    })
                    ->whereNotIn('users.role_id', config('config.ADMIN_ROLE_IDS'))
                    ->whereIn('users.id', $agents)
                    ->withTrashed() //Already filtering, this will speed up query
                    ->groupBy('users.id')
                    ->get()
                    ->summarize($now, 'avg_feedback');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Get all chats
     *
     * @param type $clientId
     * @param type $groupId
     * @param type $startDate
     * @param type $endDate
     * @param type $agentId
     * @param type $organizationTimezone
     * @return type
     */
    public static function getAllChats($clientId, $groupId, $startDate, $endDate, $agentId, $organizationTimezone)
    {
        $query = self::getAllChatMessageQuery($clientId, $groupId, $startDate, $endDate, $organizationTimezone);

        if ($agentId != '') {
            $query->where('agent_id', $agentId);
        }
        $chatMessages = $query->get();
        return ($chatMessages) ? $chatMessages->toArray() : [];
    }

    /**
     * Function to get chat message query.
     *
     * @param integer $clientId
     * @throws \ExceptionJSON_EXTRACT(message, "$.path") IS NOT NULL
     * @return QueryBuilder
     */
    private static function getAllChatMessageQuery($clientId, $groupId, $startDate, $endDate, $organizationTimezone)
    {
        $baseUrl        = \Illuminate\Support\Facades\URL::to('/');
        $systemTimezone = config('app.timezone');
        try {
            $query = self::select('chat_channels.id', 'source_type', 'u2.name AS agent_name', 'u2.email AS agent_email', DB::raw("IF(root_channel_id IS NOT NULL,root_channel_id,'') AS root_channel_id"), 'c1.identifier AS customer_identifier', DB::raw('IF(accepted_at IS NOT NULL,CONVERT_TZ(FROM_UNIXTIME(accepted_at, "%Y-%m-%d %h:%i:%s"),"' . $systemTimezone . '","' . $organizationTimezone . '"),CONVERT_TZ(FROM_UNIXTIME(agent_assigned_at, "%Y-%m-%d %h:%i:%s"),"' . $systemTimezone . '","' . $organizationTimezone . '")) AS start_time'), DB::raw('IF(terminated_at IS NOT NULL, CONVERT_TZ(FROM_UNIXTIME(terminated_at, "%Y-%m-%d %h:%i:%s"),"' . $systemTimezone . '","' . $organizationTimezone . '"), CONVERT_TZ(FROM_UNIXTIME(transferred_at, "%Y-%m-%d %h:%i:%s"),"' . $systemTimezone . '","' . $organizationTimezone . '")) AS end_time'))
                    ->join('users as u2', 'chat_channels.agent_id', 'u2.id')
                    ->join('clients as c1', 'chat_channels.client_id', 'c1.id')
                    ->whereHas('chatMessage', function($query) use ($startDate, $endDate) {
                        $query->whereBetween('chat_messages_history.created_at', [$startDate, $endDate]);
                    })
                    ->with(['chatMessage' => function ($query) use ($baseUrl, $organizationTimezone, $systemTimezone, $startDate, $endDate) {
                            $query->select([
                                'chat_channel_id',
                                DB::raw('CASE '
                                        . 'WHEN  recipient = "BOT" AND message_type = "BOT" THEN '
                                        . 'CASE WHEN JSON_EXTRACT(message, "$.path") IS NOT NULL '
                                        . 'THEN CONCAT("BOT", ":", JSON_EXTRACT(message, "$.file_name"), " url:' . $baseUrl . '" , JSON_UNQUOTE(JSON_EXTRACT(message, "$.path"))) '
                                        . 'ELSE CONCAT("BOT", ":", JSON_EXTRACT(message, "$.text")) '
                                        . 'END '
                                        . 'WHEN  recipient = "VISITOR" AND message_type = "BOT" THEN '
                                        . 'CASE WHEN JSON_EXTRACT(message, "$.path") IS NOT NULL '
                                        . 'THEN CONCAT(c.identifier, ":", JSON_EXTRACT(message, "$.file_name"), " url:' . $baseUrl . '" , JSON_EXTRACT(message, "$.path")) '
                                        . 'ELSE CONCAT(c.identifier, ":", JSON_EXTRACT(message, "$.text")) '
                                        . 'END '
                                        . 'WHEN  recipient = "VISITOR" AND message_type = "public" THEN '
                                        . 'CASE WHEN JSON_EXTRACT(message, "$.path") IS NOT NULL '
                                        . 'THEN CONCAT(IF(u1.id IS NOT NULL, u1.name, u.name), ":", JSON_EXTRACT(message, "$.file_name"), " url:' . $baseUrl . '" , JSON_UNQUOTE(JSON_EXTRACT(message, "$.path"))) '
                                        . 'ELSE CONCAT(IF(u1.id IS NOT NULL, u1.name, u.name), ":", JSON_EXTRACT(message, "$.text")) '
                                        . 'END '
                                        . 'WHEN  recipient = "AGENT" AND message_type = "public" THEN '
                                        . 'CASE WHEN JSON_EXTRACT(message, "$.path") IS NOT NULL '
                                        . 'THEN CONCAT(c.identifier, ":", JSON_EXTRACT(message, "$.file_name"), " url:' . $baseUrl . '" , JSON_UNQUOTE(JSON_EXTRACT(message, "$.path"))) '
                                        . 'ELSE CONCAT(c.identifier, ":", JSON_EXTRACT(message, "$.text")) '
                                        . 'END '
                                        . 'END as chat'),
                                DB::raw('CONVERT_TZ(FROM_UNIXTIME(chat_messages_history.created_at, "%Y-%m-%d %h:%i:%s"),"' . $systemTimezone . '","' . $organizationTimezone . '") AS date_time')
                                    ]
                            )->join('users as u', 'chat_messages_history.user_id', 'u.id')
                            ->join('clients as c', 'chat_messages_history.client_id', 'c.id')
                            ->leftJoin('users as u1', 'chat_messages_history.internal_agent_id', 'u1.id');
                        }])->where('group_id', $groupId)
                    ->whereNotIn('chat_channels.status', [self::CHANNEL_STATUS_UNPICKED, self::CHAT_STATUS_ACCEPT]);
            if ($clientId != '' || $clientId === 0) {
                $query->where('client_id', $clientId);
            }

            return $query;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * function to get the chats in the queue user groups wise.
     *
     * @return type
     */
    public static function getQueueCount($groupId = null, $userId = null)
    {
        try {
            $userQueueCount = [];
            if (is_null($userId)) {
                $organizationId = Group::getOrganizationIdByGroup($groupId);
                $adminUserId    = User::where('organization_id', $organizationId)
                        ->where('role_id', config('constants.user.role.admin'))
                        ->where('is_login', User::IS_LOGIN)
                        ->value('id');
                // get organization groups
                $groups         = Group::where('organization_id', $organizationId)->pluck('id');
                // get group users
                $userGroups     = UserGroup::select('user_id', 'group_id')
                        ->join('users', 'users.id', '=', 'user_groups.user_id')
                        ->where('users.is_login', User::IS_LOGIN)
                        ->where('user_id','!=' ,$adminUserId)
                        ->whereIn('group_id', $groups)->groupBy(['user_id', 'group_id'])
                        ->get();


                if ($adminUserId) {
                    $adminQueueCount              = self::select('group_id', DB::raw('count(*) as queue_total'))
                            ->whereIn('group_id', $groups)
                            ->whereNull('agent_id')
                            ->where('status', config('constants.CHAT_STATUS.UNPICKED'))
                            //->where('created_at', '>', Carbon::now()->subDays(config('config.queue_subtract_days'))->timestamp)
                            ->count();
                    $userQueueCount[$adminUserId] = $adminQueueCount;
                }
            } else {
                $user = User::find($userId);
                if($user->role_id == config('constants.user.role.admin')){
                 $groups         = Group::where('organization_id', $user->organization_id)->pluck('id');
                 $adminQueueCount              = self::select('group_id', DB::raw('count(*) as queue_total'))
                            ->whereIn('group_id', $groups)
                            ->whereNull('agent_id')
                            ->where('status', config('constants.CHAT_STATUS.UNPICKED'))
                            //->where('created_at', '>', Carbon::now()->subDays(config('config.queue_subtract_days'))->timestamp)
                            ->count();
                    $userQueueCount[$userId] = $adminQueueCount;
                    return $userQueueCount;
                 }
                else{
                 $groups     = UserGroup::where('user_id', $userId)->pluck('group_id');
                // get group users
                $userGroups = UserGroup::select('user_id', 'group_id')->whereIn('group_id', $groups)
                                ->where('user_id', $userId)
                                ->groupBy(['user_id', 'group_id'])->get();
                }

            }

            // get queue channels
            $groupChannelQueueCount = self::select('group_id', DB::raw('count(*) as queue_total'))
                    ->whereIn('group_id', $groups)
                    ->whereNull('agent_id')
                    ->where('status', config('constants.CHAT_STATUS.UNPICKED'))
                    //->where('created_at', '>', Carbon::now()->subDays(config('config.queue_subtract_days'))->timestamp)
                    ->groupBy('group_id')
                    ->get()
                    ->keyBy('group_id');

                foreach ($userGroups as $data) {
                    $userQueueCount[$data['user_id']] = ($userQueueCount[$data['user_id']] ?? 0) + (isset($groupChannelQueueCount[$data['group_id']]) ? $groupChannelQueueCount[$data['group_id']]['queue_total'] : 0);
            }
            return $userQueueCount;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public static function getChatCountAgent($agents, $status){

        $chatCountResult = ChatChannel::select('name', 'agent_id', DB::raw('COUNT(chat_channels.id) as count'))
                    ->join('users', 'users.id', '=', 'chat_channels.agent_id')
                    ->groupBy('agent_id')
                    ->where('chat_channels.status', $status)
                    ->whereNotNull('agent_id')
                    ->whereIn('agent_id', $agents)
                    ->get()->toArray();

        return $chatCountResult;
    }

    public static function getUniqueChatsCount($startDate, $endDate, $agentIds)
    {
        $startTimeStamp = Carbon::parse($startDate)->timestamp;
        $endTimeStamp = Carbon::parse($endDate)->addDay()->timestamp;
        $uniqueChatQuery = self::select(DB::raw('count(DISTINCT(client_id))as count_unique_chat'));
        if (is_array($agentIds)) {
            $uniqueChatQuery = $uniqueChatQuery->whereIn('agent_id', $agentIds);
        } else {
            $uniqueChatQuery = $uniqueChatQuery->where('agent_id', $agentIds);
        }
        $uniqueChatQuery = $uniqueChatQuery->where('chat_channels.created_at', '>=', $startTimeStamp)
        ->where('chat_channels.created_at', '<', $endTimeStamp)
        ->first();
        return $uniqueChatQuery->count_unique_chat;
    }


    /**
     * Function to get average first response time.
     *
     * @param string $now
     * @param array $agents
     * @throws Exception
     */
    public static function getAverageFirstResponseTimeToVisitor($now, $agents)
    {
        try {
            is_valid_date($now);
            self::select('organization_id', DB::raw('avg(waiting_time_for_visitor) as avg_first_response_to_visitor'), 'chat_channels.group_id')
                    ->join('groups', 'groups.id', '=', 'chat_channels.group_id')
            ->where(DB::raw('DATE(FROM_UNIXTIME(chat_channels.created_at))'), $now)
            ->whereNotNull('first_response_to_visitor')
            ->groupBy('organization_id')
            ->get()
            ->summarize($now, 'avg_first_response_to_visitor');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public static function getAllMissedChatClients($startDate, $endDate, $status=null)
    {

        try {
            $startTimeStamp = Carbon::parse($startDate)->timestamp;
            $endTimeStamp = Carbon::parse($endDate)->addDay()->timestamp;
            $query = self::select('identifier', DB::raw('chat_channels.id as chat_channel_id'), 'chat_channels.source_type',DB::raw('chat_channels.created_at as createdDate'),'missed_chat_actions.status', 'chat_channels.client_id')
                    ->join('clients', 'clients.id', '=', 'chat_channels.client_id')
                    ->leftJoin('missed_chat_actions', 'chat_channels.id', 'missed_chat_actions.chat_channel_id')
                    ->where(function($query) use ($startTimeStamp, $endTimeStamp){
                        $query->where('chat_channels.updated_at', '>=', $startTimeStamp);
                        $query->where('chat_channels.updated_at', '<', $endTimeStamp);
                    })
                    ->where(function($query){
                        $query->where(function($query){
                            $query->whereNotNull('agent_id');
                            $query->whereNull('accepted_at');
                            $query->whereIn('closed_via', [self::CHAT_CLOSED_VIA_VISITOR_LEFT, self::CHAT_CLOSED_VIA_AGENT_FORCE_LOGOUT, self::CHAT_CLOSED_VIA_AGENT_CLOSE]);
                        })->orWhere(function($query){
                            $query->whereNull('agent_id');
                            $query->whereNotNull('queued_at');
                            $query->where('closed_via', self::CHAT_CLOSED_VIA_VISITOR_LEFT);
                        });
                    });
                   /* Status filter Query*/
                   if(isset($status) && isset(array_flip(config('constants.MISSED_CHAT_ACTION'))[$status])) {
                     $query->where(function($queryy) use ($status) {
                        $status ? $queryy->where('missed_chat_actions.status', $status) : $queryy->whereNull('missed_chat_actions.status');
                     });
                   }
                   /* Status filter Query*/
                   $query = $query->where('organization_id', Auth::user()->organization->id)
                    ->orderBy('chat_channels.created_at', 'DESC')
                    ->paginate(config('config.PER_PAGE_SIZE_MISSED_CHAT'));
            return $query;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

}
