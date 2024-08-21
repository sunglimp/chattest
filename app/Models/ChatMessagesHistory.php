<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Models\ChatTags;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Doctrine\DBAL\Query\QueryBuilder;

class ChatMessagesHistory extends Model
{
    const SEARCH_KEYWORD = 1;
    const SEARCH_TAG = 2;
    const SEARCH_INTERNAL_COMMENTS = 3;
    const PER_PAGE_SIZE_ARCHIVE = 15;

    protected $table      = 'chat_messages_history';
    protected $dateFormat = 'U';
    public $timestamps    = true;
    protected $dates = [
        'created_at'
    ];
    protected $casts    = [
            //'created_at' => 'datetime:Y-m-d H:i:s',
    ];
    protected $fillable = ['organization_id', 'chat_channel_id', 'client_id', 'user_id', 'root_channel_id', 'identifier', 'message', 'recipient', 'message_type','source_type'];


    public function client()
    {
        return $this->hasOne('App\Models\Client', 'id', 'client_id');
    }

    public function chats()
    {
        return $this->belongsToMany('App\Models\ChatChannels', 'id', 'chat_channel_id');
    }

    public static function getOldArchiveMessages($clientId, $channelId, $startDate, $endDate, $missedChat = false)
    {
        try {
            $query = self::getChatMessageQuery($clientId, $missedChat);
            $historyMessages = $query
                    ->where('root_channel_id', $channelId)
                    ->whereBetween('chat_messages_history.created_at', [$startDate, $endDate])
                    ->get();
                    //->paginate(config('config.MESSAGE_LIMIT'));
            return $historyMessages;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get client list in archive chats.
     *
     * @param integer $userId
     * @param string $startDate
     * @param string $endDate
     * @param string $type
     * @param string $search
     * @throws \Exception
     */
    public static function getClientList($userIds, $startDate, $endDate, $type, $search, $organizationId, $isTicket = false, $ticketType, $page=1)
    {
        info("================================Ticket Status ::" . $isTicket);
        try {
            $allTagged = false; //will be true always in case of tag search
            $clientList = ChatMessagesHistory::select('clients.raw_info', 'chat_messages_history.client_id','chat_messages_history.chat_channel_id', 'chat_messages_history.created_at as createdDate', 'chat_messages_history.identifier', 'chat_messages_history.source_type');
            $clientList->join('clients', function ($query) use ($search) {
                $query->on('clients.id', '=', 'chat_messages_history.client_id');
            });
            $clientList->whereBetween('chat_messages_history.created_at', [$startDate, $endDate]);
            $clientList->whereIn('user_id', $userIds);
            if ($type == self::SEARCH_TAG) {
                $allTagged = true;
                $clientList->join('chat_tags', function ($query) use ($search) {
                    $query->on('chat_tags.chat_channel_id', '=', 'chat_messages_history.chat_channel_id');
                    if (!empty($search)){
                        $query->whereIn('chat_tags.tag_id',$search);
                    }

                });
            } else {
                $clientList->leftJoin('chat_tags', 'chat_tags.chat_channel_id', '=', 'chat_messages_history.chat_channel_id');
                $clientList->addSelect(DB::raw('IF(MAX(chat_tags.id) IS NOT NULL , 1, 0) as tagged'));
            }
            if ($isTicket === 'true') {
                $clientList->join('chat_channels', function ($query) use ($ticketType) {
                    $query->on('chat_channels.id', '=', 'chat_messages_history.chat_channel_id')
                        ->where('chat_channels.ticket_status', ChatChannel::CHAT_STATUS_PENDING)
                        ->where('chat_channels.ticket_type', $ticketType);
                });
            }

            if ($type == self::SEARCH_INTERNAL_COMMENTS) {
                $clientList->where(DB::raw('lower(JSON_EXTRACT(message, "$.text"))'), 'like', "%$search%")
                    ->where('message_type', ChatMessage::MESSAGE_TYPE_INTERNAL);
            }
            if ($type == self::SEARCH_KEYWORD && $search != '') {
                $clientList->where(function ($query) use ($search) {
                    $query->where(DB::raw('lower(JSON_EXTRACT(message, "$.text"))'), 'like', "%$search%")
                      ->orWhere(DB::raw('chat_messages_history.identifier'), 'like', "%$search%")
                      ->orWhere(DB::raw('lower(JSON_EXTRACT(clients.raw_info, "$.*"))'), 'like', "%$search%");
                });
            }
            $clientList->orderBy('chat_messages_history.created_at', 'DESC')
            ->groupBY('chat_messages_history.root_channel_id')
            ->offset(($page-1) * self::PER_PAGE_SIZE_ARCHIVE)
            ->limit(self::PER_PAGE_SIZE_ARCHIVE);
            $dataClient = $clientList->get();
            return [$dataClient, $allTagged];
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public static function recentMessagePlusUnreadCount($channelId)
    {
        $msg = self::where('chat_channel_id', $channelId)->whereNull('read_at')->orderby('id', 'desc')->get();
        return ['unread_count' => count($msg), 'recent_message' => empty($msg[0]->message) ? '' : json_decode($msg[0]->message, true) ];
    }

    public static function unreadCountByRootChannel($channelId)
    {
        $msg = self::where('root_channel_id', $channelId)->whereNull('read_at')->orderby('id', 'desc')->get();
        return ['unread_count' => count($msg), 'recent_message' => empty($msg[0]->message) ? null : json_decode($msg[0]->message, true)];
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get average interactions.
     *
     * @param unknown $now
     * @throws Exception
     */
    public static function getAverageInteractions($now, $agents)
    {
        try {
            is_valid_date($now);

            User::select(
                'users.id  as agent_id',
                'users.organization_id',
                DB::raw('count(chat_messages_history.id)/COUNT(DISTINCT chat_messages_history.chat_channel_id) as avg_interaction')
            )->join('chat_messages_history', function ($query) use ($now) {
                $query->on('chat_messages_history.user_id', '=', 'users.id')
                ->where(DB::raw('DATE(FROM_UNIXTIME(chat_messages_history.created_at))'), $now)
                ->where('chat_messages_history.message_type', '=', 'public');
            })->whereNotIn('users.role_id', config('config.ADMIN_ROLE_IDS'))
            ->whereIn('users.id', $agents)
            ->withTrashed() // We alreday checked deleted agents so this will reduce query execution time
            ->groupBy('users.id')
            ->get()
            ->summarize($now, 'avg_interaction');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get average response time.
     * @param type $now
     * @param type $agents
     * @throws \Exception
     */
    public static function getAverageresponseTime($now, $agents=[])
    {
        try {
            $startTimeStamp = Carbon::parse($now)->timestamp;
            $endTimeStamp = Carbon::parse($now)->addDay()->timestamp;
            $query = DB::table('chat_messages_history')->select(
                'user_id as agent_id',
                 DB::raw('AVG(response_within) as avg_response_time'),
                'organization_id'
                )
            ->whereIn('user_id', $agents)
            ->where(function($query) use($startTimeStamp, $endTimeStamp){
                $query->where('created_at', '>=', $startTimeStamp);
                $query->where('created_at', '<', $endTimeStamp);
            })
            ->where('response_within', '>', 0)
            ->groupBy('agent_id')
            ->get()
            ->summarize($now, 'avg_response_time');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     *
     * @param unknown $clientId
     */
    public static function getBannedClientMessages($clientId)
    {
        $query = self::getChatMessageQuery($clientId);
        return $query->get();
    }

    /**
     * Function to get chat message query.
     *
     * @param integer $clientId
     * @throws \Exception
     * @return QueryBuilder
     */
    private static function getChatMessageQuery($clientId, $missedChat = false)
    {
        try {
            $query = ChatMessagesHistory::select(
                'message',
                'recipient',
                'message_type',
                'chat_messages_history.created_at',
                'u.timezone',
                'source_type',
                'chat_messages_history.created_at as chat_created_at',
                DB::raw('IF(u1.id IS NOT NULL, u1.name, u.name) as agent_display_name')
            );
            // Join Type by condition of Missed Chat
            if ($missedChat) {
                $query->leftJoin('users as u', function($join){
                    $join->on('chat_messages_history.user_id', '=', 'u.id');
                });
            }else {
                $query->join('users as u', function($join){
                    $join->on('chat_messages_history.user_id', '=', 'u.id');
                });
            }
            $query->leftJoin('users as u1', 'chat_messages_history.internal_agent_id', 'u1.id')
            ->where('client_id', $clientId);
            return $query;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public static function getChatMessages($clientId, $channelId, $startDate, $endDate)
    {
        $query = self::getChatMessageDownloadQuery($clientId);
        $chatMessages = $query
            ->where('root_channel_id', $channelId)
            ->whereBetween('chat_messages_history.created_at', [$startDate, $endDate])
            ->pluck('chat');
        return ($chatMessages) ? $chatMessages->toArray() : [];
    }

    /**
     * Function to get chat message query.
     *
     * @param integer $clientId
     * @throws \ExceptionJSON_EXTRACT(message, "$.path") IS NOT NULL
     * @return QueryBuilder
     */
    private static function getChatMessageDownloadQuery($clientId)
    {
        $baseUrl = \Illuminate\Support\Facades\URL::to('/');
        $system_timezone = config('app.timezone');
        $user_timezone  = Auth()->user()->timezone;
        try {
            $query = ChatMessagesHistory::select(
                DB::raw('CASE '
                        . 'WHEN  recipient = "BOT" AND message_type = "BOT" THEN '
                            . 'CASE WHEN JSON_EXTRACT(message, "$.path") IS NOT NULL '
                                . 'THEN CONCAT(CONVERT_TZ(FROM_UNIXTIME(chat_messages_history.created_at, "%Y-%m-%d %h:%i:%s"),"'. $system_timezone .'","'. $user_timezone .'"), " " ,"BOT", ":", JSON_EXTRACT(message, "$.file_name"), " url:'. $baseUrl.'" , JSON_UNQUOTE(JSON_EXTRACT(message, "$.path"))) '
                                . 'ELSE CONCAT(CONVERT_TZ(FROM_UNIXTIME(chat_messages_history.created_at, "%Y-%m-%d %h:%i:%s"),"'. $system_timezone .'","'. $user_timezone .'"), " " ,"BOT", ":", JSON_EXTRACT(message, "$.text")) '
                            . 'END '
                        . 'WHEN  recipient = "VISITOR" AND message_type = "BOT" THEN '
                            . 'CASE WHEN JSON_EXTRACT(message, "$.path") IS NOT NULL '
                                . 'THEN CONCAT(CONVERT_TZ(FROM_UNIXTIME(chat_messages_history.created_at, "%Y-%m-%d %h:%i:%s"),"'. $system_timezone .'","'. $user_timezone .'"), " " ,c.identifier, ":", JSON_EXTRACT(message, "$.file_name"), " url:'. $baseUrl.'" , JSON_EXTRACT(message, "$.path")) '
                                . 'ELSE CONCAT(CONVERT_TZ(FROM_UNIXTIME(chat_messages_history.created_at, "%Y-%m-%d %h:%i:%s"),"'. $system_timezone .'","'. $user_timezone .'"), " " ,c.identifier, ":", JSON_EXTRACT(message, "$.text")) '
                            . 'END '
                        . 'WHEN  recipient = "VISITOR" AND message_type = "public" THEN '
                            . 'CASE WHEN JSON_EXTRACT(message, "$.path") IS NOT NULL '
                                . 'THEN CONCAT(CONVERT_TZ(FROM_UNIXTIME(chat_messages_history.created_at, "%Y-%m-%d %h:%i:%s"),"'. $system_timezone .'","'. $user_timezone .'"), " " ,IF(u1.id IS NOT NULL, u1.name, u.name), ":", JSON_EXTRACT(message, "$.file_name"), " url:'. $baseUrl.'" , JSON_UNQUOTE(JSON_EXTRACT(message, "$.path"))) '
                                . 'ELSE CONCAT(CONVERT_TZ(FROM_UNIXTIME(chat_messages_history.created_at, "%Y-%m-%d %h:%i:%s"),"'. $system_timezone .'","'. $user_timezone .'"), " " ,IF(u1.id IS NOT NULL, u1.name, u.name), ":", JSON_EXTRACT(message, "$.text")) '
                            . 'END '
                        . 'WHEN  recipient = "AGENT" AND message_type = "public" THEN '
                            . 'CASE WHEN JSON_EXTRACT(message, "$.path") IS NOT NULL '
                                . 'THEN CONCAT(CONVERT_TZ(FROM_UNIXTIME(chat_messages_history.created_at, "%Y-%m-%d %h:%i:%s"),"'. $system_timezone .'","'. $user_timezone .'"), " " ,c.identifier, ":", JSON_EXTRACT(message, "$.file_name"), " url:'. $baseUrl.'" , JSON_UNQUOTE(JSON_EXTRACT(message, "$.path"))) '
                                . 'ELSE CONCAT(CONVERT_TZ(FROM_UNIXTIME(chat_messages_history.created_at, "%Y-%m-%d %h:%i:%s"),"'. $system_timezone .'","'. $user_timezone .'"), " " ,c.identifier, ":", JSON_EXTRACT(message, "$.text")) '
                            . 'END '
                . 'END as chat')
            )
            ->join('users as u', 'chat_messages_history.user_id', 'u.id')
            ->join('clients as c', 'chat_messages_history.client_id', 'c.id')
            ->leftJoin('users as u1', 'chat_messages_history.internal_agent_id', 'u1.id')
            ->where('client_id', $clientId);
            return $query;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public static function getSourceType($channelId)
    {
        return self::select('source_type')->where('root_channel_id', $channelId)->first();
    }

    /**
     *
     * @param type $userIds array
     * @param type $tagIds
     * @param type $startDate
     * @param type $endDate
     * @param type $organization_id
     * @return type
     */
    public static function getChatTagReports($userIds, $tagIds, $startDate, $endDate, $organization_id)
    {
        $system_timezone = config('app.timezone');
        $user_timezone  = Auth()->user()->timezone;
        $identifierMaskPermission = Auth()->user()->checkPermissionBySlug('identifier_masking');

        $identifierLabel = default_trans($organization_id.'/archive.ui_elements_messages.identifier', __('default/archive.ui_elements_messages.identifier'));
        $dateLabel = default_trans($organization_id.'/archive.ui_elements_messages.date', __('default/archive.ui_elements_messages.date'));
        $timeLabel = default_trans($organization_id.'/archive.ui_elements_messages.time', __('default/archive.ui_elements_messages.time'));
        $sourceTypeLabel = default_trans($organization_id.'/archive.ui_elements_messages.source_type', __('default/archive.ui_elements_messages.source_type'));
        $tagsLabel = default_trans($organization_id.'/archive.ui_elements_messages.tags', __('default/archive.ui_elements_messages.tags'));

        return ChatMessagesHistory::select(
                'clients.identifier as '.$identifierLabel,
                DB::raw('DATE(CONVERT_TZ(FROM_UNIXTIME(chat_messages_history.created_at, "%Y-%m-%d"),"'. $system_timezone .'","'. $user_timezone .'")) as "'.$dateLabel.'"'),
                DB::raw('TIME(CONVERT_TZ(FROM_UNIXTIME(chat_messages_history.created_at, "%Y-%m-%d %h:%i"),"'. $system_timezone .'","'. $user_timezone .'")) as "'.$timeLabel.'"'),
                'chat_channels.source_type as '.$sourceTypeLabel ,'chat_tags.tag_name as '.$tagsLabel
            )
            ->join('clients', function ($query) {
                $query->on('clients.id', '=', 'chat_messages_history.client_id');
            })
            ->join('chat_tags', function ($query) use ($tagIds) {
                $query->on('chat_tags.chat_channel_id', '=', 'chat_messages_history.chat_channel_id');
                if (!empty($tagIds)) {
                    $query->whereIn('chat_tags.tag_id', $tagIds);
                }
            })
            ->join('chat_channels', function ($query) {
                $query->on('chat_channels.id', '=', 'chat_messages_history.chat_channel_id');
            })

            ->whereIn('chat_messages_history.user_id', $userIds)
            ->whereBetween('chat_messages_history.created_at', [$startDate, $endDate])
            ->groupBY(['chat_messages_history.client_id','chat_tags.id'])
            ->get()->each(function($tags) use ($identifierLabel,$identifierMaskPermission) {
                $tags->{$identifierLabel} = $identifierMaskPermission ? mask($tags->{$identifierLabel}) : $tags->{$identifierLabel};
            });

    }
}
