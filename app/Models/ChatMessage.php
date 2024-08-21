<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Agent;
use App\Models\Client;
use App\Events\InformAgentPrivateChannel;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use function GuzzleHttp\json_decode;
use Illuminate\Support\Facades\Storage;
use App\User;
use Illuminate\Support\Facades\Redis;

class ChatMessage extends Model
{
    protected $dateFormat = 'U';
    public $timestamps = true;

    protected $fillable = [
        'chat_channel_id',
        'message',
        'internal_agent_id',
        'recipient',
        'message_type',
        'response_within',
        'source_type'
    ];

    const MESSAGE_TYPE_PUBLIC = 'public';
    const MESSAGE_TYPE_INTERNAL = 'internal';
    const MESSAGE_TYPE_BOT = 'BOT';
    const MESSAGE_TYPE_TRANSFER = 'transfer';

    const RECIPIENT_VISITOR = 'VISITOR';
    const RECIPIENT_AGENT = 'AGENT';

     /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'read_at'
    ];

    protected $casts = [
        //'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function channel()
    {
        return $this->belongsTo(ChatChannel::class, 'chat_channel_id');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRecent($query, $take = null)
    {
        $take = $take ?? config('chat.load_message_count');
        return $query->latest()
                ->take($take)
                ->get()
                ->sortBy('id');
    }

    public function scopeLoadPrevious($query, $offset, $take = null)
    {
        $take = $take ?? config('chat.load_message_count');
        return $query->latest()
                ->where('id', '<', $offset)
                ->take($take)
                ->get()
                ->sortBy('id');
    }

    public function scopeOfChannel($query, $channel)
    {
        $query->select('chat_messages.*');
        $query->where('channel_name', $channel);
        return $query->rightJoin('chat_channels', 'chat_channels.id', '=', 'chat_messages.chat_channel_id');
    }


    public function setGroupIdAttribute($groupId)
    {
        $this->group_id = $groupId;
    }

    public function setAgentIdAttribute($agentId)
    {
        $this->agent_id = $agentId;
    }

    /**
     *
     * @param integer $channelId
     */
    public static function getMessages($channelId, $messageOffset, $userId)
    {
        try {
            $rootChannelId = ChatChannel::select('root_channel_id')
                                                  ->where('id', $channelId)
                                                   ->first();
            $rootChannelId = $rootChannelId->root_channel_id ??  $channelId;
            $currentMessages = self::getCurrentMessages($channelId, $userId);
            $historyMessages = self::getOldMessages($rootChannelId, $messageOffset);
            $finalMessages = $historyMessages->concat($currentMessages);
            return $finalMessages;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     *
     * @param integer $channelId
     */
    public static function getHistoryMessages($channelId, $userId)
    {
        try {
            $rootChannel = ChatChannel::select('root_channel_id','client_id')
                                                  ->where('id', $channelId)
                                                   ->first();
            $rootChannelId = $rootChannel->root_channel_id ??  $channelId;
            $clientId = $rootChannel->client_id;
            $historyMessages = self::getHistoryChat($rootChannelId, $clientId);
            return $historyMessages;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * FUnction to get current messages for corresponding channel.
     *
     * @param integer $channelId
     * @throws Exception
     */
    private static function getCurrentMessages($channelId, $userId)
    {
        try {
            $agentName = Agent::displayName($userId);
            $agentName = "'".$agentName."'";
            return ChatMessage::select('message', 'internal_agent_id', 'recipient', 'message_type', 'chat_messages.created_at', DB::raw("$agentName as agent_display_name"),'source_type')
                        ->leftJoin('users as u1', 'chat_messages.internal_agent_id', 'u1.id')
                        ->where('chat_channel_id', $channelId)
                        ->orderby('created_at')
                        ->get();
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to get old messages.
     *
     * @param integer $rootChannelId
     * @throws \Exception
     */
    private static function getOldMessages($rootChannelId, $messageOffset)
    {
        try {
            $historyMessages = ChatMessagesHistory::select(
                'internal_agent_id',
                'message',
                'recipient',
                'message_type',
                'chat_messages_history.created_at',
                'u.name as agent_display_name',
                'source_type'
            )
                                        ->join('users as u', 'chat_messages_history.user_id', 'u.id')
                                        ->where('root_channel_id', $rootChannelId)
                                        ->limit(config('config.MESSAGE_LIMIT'))
                                        ->offset($messageOffset*config('config.MESSAGE_LIMIT'))
                                        ->orderby('created_at')
                                        ->get();
            return $historyMessages;//->reverse();
            //@TODO - Multiple Joins can be avoided using "basic php iteration & cache"
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    private static function getHistoryChat($rootChannelId, $clientId)
    {
        try {
            $historyMessages = ChatMessagesHistory::select(
                'internal_agent_id',
                'message',
                'recipient',
                'message_type',
                'chat_messages_history.created_at',
                'u.name as agent_display_name',
                'source_type'
            )
                                        ->join('users as u', 'chat_messages_history.user_id', 'u.id')
                                        ->where('root_channel_id', '!=', $rootChannelId)
                                        ->where('client_id', $clientId)
                                        ->orderBy('chat_messages_history.id', 'desc')
                                        ->paginate(config('config.MESSAGE_LIMIT'));

            return $historyMessages;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function chatChannel()
    {
        return $this->belongsTo(ChatChannel::class);
    }

    public function isInternalChat()
    {
        return isset($this->message_type) && ($this->message_type == ChatMessage::MESSAGE_TYPE_INTERNAL);
    }

    public function createWithInternalChat()
    {

        if ($this->isInternalChat()) {

            $request = request();
            $aMessage['channel_name'] = $request->get('channel_name');
            $aMessage['message']      = $request->get('message'); //reverting json encode before passing to event
            if ($request->get('channel_agent_id')) {
                //Means internal comment just started, so making agent to listen channel
                $isAdded = InternalCommentChannel::addInternalCommentChannel($request->get('chat_channel_id'), $request->get('agent_id'));
                if ($isAdded === true) {

                    $clientInfo = Client::details($request->get('client_id'));

                     // Get agent name and role
            $agentName = '';
            $role = '';
            $agentDetail = User::getAgentDetail($request->get('channel_agent_id'));
            if($agentDetail){
            $agentName = $agentDetail->agent_name;
            $role = $agentDetail->role;
            }

                    broadcast(new InformAgentPrivateChannel([
                        'event'               => config('broadcasting.events.new_chat'),
                        'agent_id'            => $request->get('agent_id'),
                        'group_id'            => $request->get('group_id'),
                        'id'                  => $request->get('chat_channel_id'),
                        'channel_name'        => $request->get('channel_name'),
                        'client_id'           => $request->get('client_id'),
                        'source_type'           => $request->get('source_type'),
                        'agent_name'          => $agentName,
                        'role'                => $role,
                        'client_display_name' => $request->get('client_display_name'),
                        'client_raw_info'     => $clientInfo['raw_info'],
                        'unread_count'        => 1,
                        'recent_message'      => $request->get('message'),
                        'channel_type'        => 'internal_comment',
                        'channel_agent_id'    => $request->get('channel_agent_id')
                    ]))->toOthers();
                    send_chat_notification($request->get('agent_id'), config('constants.CHAT_NOTIFICATION_EVENTS.INTERNAL_COMMENT'));
                    return;
                }
            }
            $aMessage['event']               = config('broadcasting.events.new_internal_comment');
            $aMessage['agent_id']            = $request->get('agent_id');
            $aMessage['internal_agent_id'] = $request->get('internal_agent_id');
            $aMessage['sender_display_name'] = $request->get('sender_display_name');
            broadcast(new InformAgentPrivateChannel($aMessage))->toOthers();
        }
    }


    public function isVisitorMessage()
    {
        return $this->recipient == self::RECIPIENT_AGENT;
    }

    public function isAgentMessage()
    {
        return $this->recipient == self::RECIPIENT_VISITOR;
    }

    public function isPublicMessage()
    {

        return $this->message_type == self::MESSAGE_TYPE_PUBLIC;
    }

    /**
     * Function to save message.
     *
     * @throws \Exception
     */
    public static function saveMessage($request)
    {
        $sourceType = Redis::get("source_type" . $request->chat_channel_id)??'web';

        try {
            if(!empty($request->chat_channel_id)){
            $aMessage = [];
            $aMessage['chat_channel_id'] = $request->chat_channel_id;
            $aMessage['message'] = json_encode($request->message);
            $aMessage['recipient'] = $request->recipient;
            $aMessage['message_type'] = $request->message_type;
            $aMessage['source_type'] = $sourceType;
            $aMessage['internal_agent_id'] = $request->internal_agent_id ?? null;
            return self::create($aMessage);
            }
            return ;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to save attachment messages.
     *
     * @param array $requestParams
     * @param string $fileName
     */
    public static function saveAttachmentMessages($requestParams, $fileName, $chatId, $filePath)
    {
        try {
            $request = new \Illuminate\Http\Request();

            $file = $requestParams['file'];
            $filePath = Storage::url($filePath);
            $message = array(
                'chat_channel_id' => $chatId,
                'message' => array('text' => null, 'file_name'=> $file->getClientOriginalName(), 'extension' => $file->getClientOriginalExtension(), 'size' => $file->getSize()* config('config.FILE_CONVERSION.FACTOR'), 'hash_name' => $fileName, 'path'=>$filePath),
                'recipient' => 'AGENT',
                'message_type' =>  'public',
                'channel_name' => $requestParams['channel_name']
            );

            $request->replace($message);
            return self::saveMessage($request);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Function to upload bot attachments.
     *
     * @param unknown $requestParams
     * @throws Exception
     * @return unknown
     */
    public static function uploadBotAttachments($requestParams)
    {
        try {
            $file = $requestParams['file'];
            $channelName = $requestParams['channel_name'];
            $chatId = ChatChannel::getActiveChannelId($channelName);
            if ($chatId !== false) {
                $fileName = get_file_name($file);
                $chatChannel = ChatChannel::find($chatId);
                $organizationId = $chatChannel->agent->organization_id?? Group::find($chatChannel->group_id)->organization_id;
                $filePath = upload_file($file, $fileName, $organizationId, 'chat');

                $chatMessage = ChatMessage::saveAttachmentMessages($requestParams, $fileName, $chatId, $filePath);

                $isAttched = ChatAttachment::saveData($file, $filePath, $chatMessage);
                return $isAttched;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public static function transferMessagesToHistory($channelData=[])
    {
        $chatHistory = [];
        $messages    = ChatMessage::where('chat_channel_id', $channelData['id'])->orderby('created_at')->get()->toArray();
        $organizationId = Group::find($channelData['group_id'])->organization_id;
        $clientInfo = Client::find($channelData['client_id']);
        $identifier = $clientInfo->identifier ?? '';
        foreach ($messages as $message) {

            $chatHistory[] = [
                'organization_id' => $organizationId,
                'chat_channel_id' => $channelData['id'],
                'client_id'       => $channelData['client_id'],
                'user_id'         => $channelData['agent_id'],
                'root_channel_id' => $channelData['root_channel_id'] ?? $channelData['id'],
                'identifier'      => $identifier,
                'internal_agent_id' => $message['internal_agent_id'],
                'message'         => $message['message'],
                'read_at'         => $message['read_at'],
                'recipient'       => $message['recipient'],
                'message_type'    => $message['message_type'],
                'response_within' => $message['response_within'],
                'source_type'     => $message['source_type'],
                'created_at'      => $message['created_at'],
                'updated_at'      => $message['updated_at'],
                'deleted_at'      => $message['deleted_at'],
            ];
        }
        ChatMessagesHistory::insert($chatHistory);
        ChatMessage::where('chat_channel_id', $channelData['id'])->delete();
    }

    public static function recentMessagePlusUnreadCount($channelId)
    {
        $msg = self::where('chat_channel_id', $channelId)->whereNull('read_at')->orderby('id', 'desc')->get();
        return ['unread_count' => count($msg), 'recent_message' => empty($msg[0]->message) ? null : json_decode($msg[0]->message, true) ];
    }

    /**
     *
     * @param type $system_timezone
     * @param type $params
     * @param type $identifier
     * @param type $responder
     * @param type $startDate
     * @param type $endDate
     * @param type $agentIds
     * @return type
     */
    public static function getChatAgentWise($system_timezone, $params, $identifier, $responder, $startDate, $endDate, $agentIds){

        $query = DB::select('select c.raw_info, cmh.source_type, ch.waiting_time_for_visitor as waiting_time_for_visitor, DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(cmh.created_at),"'.$system_timezone.'","'. $params['user_time_zone'].'"),'
                . ' \'%d/%m/%Y\') AS Date, DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(cmh.created_at),'
                . '"'.$system_timezone.'","'. $params['user_time_zone'] . '"), \'%r\') AS Time,'
                . ' '.$responder.' as Number, (CASE WHEN (ch.status = 4 AND ch.accepted_at IS NOT NULL) THEN "Chat Resolved" WHEN (ch.status = 5 AND ch.accepted_at IS NOT NULL) THEN "Chat Closed By Visitor" WHEN (ch.accepted_at IS NULL AND ch.closed_via = 2) THEN "In Session Timeout" WHEN (ch.accepted_at IS NULL AND ch.closed_via IN (3,4,5)) THEN "Chat Missed" ELSE "" END ) As Result,'
                . ' COALESCE(ch.feedback, "") as Rating, COALESCE(GROUP_CONCAT(ct.tag_name), "") AS Tag,'
                . ' (CASE WHEN cmh.message_type="BOT" AND cmh.recipient="VISITOR" '
                . ' THEN ' . $responder. ' WHEN cmh.message_type="BOT" AND cmh.recipient="BOT" THEN "BOT" WHEN cmh.recipient="AGENT" THEN ' . $responder . ''
                . ' WHEN cmh.recipient="VISITOR" THEN u.name END) AS Responder, (CASE WHEN  JSON_EXTRACT(message, "$.path") is null  THEN "Text" ELSE "Attachment" END ) As Type,'
                . ' (CASE WHEN  JSON_EXTRACT(message, "$.path") is null  THEN REPLACE(message->>"$.text", "\r\n", "\n") ELSE message->> "$.file_name" END ) As Message_Body '
                . 'from chat_messages_history cmh inner join chat_channels ch '
                . 'on cmh.chat_channel_id=ch.id inner join users u on cmh.user_id=u.id inner join '
                . 'clients c ON cmh.client_id=c.id left join chat_tags ct on ct.chat_channel_id=ch.id  '
                . 'where cmh.created_at >= ' . $startDate . ' and cmh.created_at <= ' . $endDate .
                ' and cmh.user_id in ('.implode(',', $agentIds). ') group by cmh.id order by cmh.id asc');

        return $query;

    }
}
