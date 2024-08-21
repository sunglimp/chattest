<?php

namespace App\Listeners;

use App\Events\ChatTransfer;
use App\Events\InformAgentPrivateChannel;
use App\Events\InformVisitorChannel;
use App\Jobs\SendChatToMlModel;
use App\Models\ChatMessage;
use App\Models\ChatChannel;
use App\Models\ChatMessagesHistory;
use App\Models\Client;
use App\User;
use App\Models\InternalCommentChannel;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Models\PermissionSetting;
use App\Models\Group;
use Illuminate\Support\Facades\Redis;
use App\Models\OrganizationRolePermission;
use Cache;

class MakeChatArchivedListener implements ShouldQueue
{

    const CHANNEL_STATUS_TRANSFERED = 3;
    const EVENT_NEW_CHAT            = 'new_chat';
    const CHAT_INTERNAL_TRANSFER    = 'Chat is transferred internally';
    const CHAT_GROUP_TRANSFER       = 'Chat is transferred externally';
    const TYPE_MESSAGE_TRANSFER     = 'transfer';

    public $queue = 'chat_archived_listener';

    public function __construct()
    {

    }

    public function handle($event)
    {
        $chatHistory = [];
        $channelData = ChatChannel::find($event->channelId)->toArray();
        ChatMessage::transferMessagesToHistory($channelData);
        $this->removeResponseDelayCacheKey($channelData);
        broadcast(new InformAgentPrivateChannel([
            'event'    => $channelData['status'] == ChatChannel::CHANNEL_STATUS_TERMINATED_BY_VISITOR
                        ? 'chat_removed_by_visitor' : 'chat_removed',
            'agent_id' => $channelData['agent_id'],
            'is_session_timeout' => $event->isSessionTimeout ?? null,
            'id'       => $event->channelId,
            ]))->toOthers();
        //Informing visitor in case of chat close by agent
        $user = User::find($channelData['agent_id']);
        if ($channelData['status'] == ChatChannel::CHANNEL_STATUS_TERMINATED_BY_AGENT) {
          if(isset($event->allLogout) && $event->allLogout == 1){
            $organizationId = Group::getOrganizationIdByGroup($channelData['group_id']);
            $messageData = PermissionSetting::where('organization_id', $organizationId)->where('permission_id', config('constants.PERMISSION.OFFLINE-FORM'))->select('settings->message as message')->first();
             $offlineData['show_offline_form'] = OrganizationRolePermission::where('organization_id', $organizationId)
                    ->where('role_id', config('constants.user.role.admin'))
                    ->where('permission_id', config('constants.PERMISSION.OFFLINE-FORM'))
                    ->exists();

            /**
             * @todo vatika is hardcoded need to be dynamic or removed
             */
            $message    = (($offlineData['show_offline_form'])
                            && ($messageData->message != 'null'))
                                ? json_decode($messageData->message,'JSON_NUMERIC_CHECK')
                                : __('message.no_agent_online', ['organization' => 'Vatika']);

            broadcast(new InformVisitorChannel([
                'event'    => 'chat_close_by_agent',
                'channel_name' => $channelData['channel_name'],
                'show_feedback_form' =>false,
                'message'=> $message
            ]))->toOthers();
          }
          else{
            broadcast(new InformVisitorChannel([
                'event'    => 'chat_close_by_agent',
                'channel_name' => $channelData['channel_name'],
                'show_feedback_form' =>$user->checkPermissionBySlug('chat-feedback')
            ]))->toOthers();
          }
        }
        info("========================ML MODEL in ARCHIVE LISTNER==================");

        if(!is_null($user) && $user->checkPermissionBySlug('classified_chat')) {

            SendChatToMlModel::dispatch($channelData['agent_id'], $event->channelId)->onQueue('mlprocess');

        }


    }

    public function ChatTransferInternal($event)
    {
        $channel                        = $this->markChannelTransferred($event->channelId);
        $transferredBy                  = $channel->agent_id;
        $channelData                    = [];
        $channelData['channel_name']    = $channel->channel_name;
        $channelData['token']           = $channel->token;
        $channelData['end_point']      = $channel->end_point;
        $channelData['group_id']        = $channel->group_id;
        $channelData['client_id']       = $channel->client_id;
        $channelData['agent_id']        = $event->agentId;
        $channelData['parent_id']       = $channel->id;
        $channelData['source_type']     = $channel->source_type;
        $channelData['root_channel_id'] = $channel->root_channel_id ?? $channel->id;
        $channelData['via_internal_transfer'] = 1;
        $origin_agent_id                = $channel->agent_id;
        $channel = ChatChannel::create($channelData);
        // save source type to redis
        $expiredAt = config('config.source_type_expire');
        Redis::set("source_type".$channel->id, $channel->source_type,'EX',$expiredAt);

        event(new ChatTransfer($origin_agent_id, $event->channelId));
        $this->handle($event);
        $channelData['id']              = $channel->id;
        $channelData['channel_name']    = $channel->channel_name;
        $channelData['unread_count']    = 1;
        $channelData['recent_message']  = self::CHAT_INTERNAL_TRANSFER;
        $channelData['comments']        = $event->chatTransferData['comment'] ?? '';
        $channelData['transferred_by']  = $transferredBy;
        $this->handleInternalComments($event->channelId, $event->agentId);

        $this->informChatTransferToAgent($channelData);
        $this->storeTransferMessage($channelData);
    }

    public function ChatTransferExternal($event)
    {
        $channel                        = $this->markChannelTransferred($event->channelId);

        $transferredBy                  = $channel->agent_id;


        event(new ChatTransfer($channel->agent_id, $event->channelId));

        $channelData                    = [];
        $channelData['channel_name']    = $channel->channel_name;
        $channelData['token']           = $channel->token;
        $channelData['end_point']      = $channel->end_point;
        $channelData['group_id']        = $event->groupId;
        $channelData['client_id']       = $channel->client_id;
        $channelData['source_type']     = $channel->source_type;
        $channelData['parent_id']       = $channel->id;
        $channelData['root_channel_id'] = $channel->root_channel_id ?? $channel->id;
        $channel                        = ChatChannel::create($channelData);

        // save source type to redis
        $expiredAt = config('config.source_type_expire');
        Redis::set("source_type".$channel->id, $channel->source_type,'EX',$expiredAt);

        $this->handle($event);
        $channelData['id']              = $channel->id;
        $channelData['channel_name']    = $channel->channel_name;
        $channelData['unread_count']    = 1;
        $channelData['recent_message']  = self::CHAT_GROUP_TRANSFER;
        $channelData['agent_id']        = $channel->agent_id;
        $channelData['comments']        = $event->chatTransferData['comment'] ?? '';
        $channelData['transferred_by']  = $transferredBy;
        $this->storeTransferMessage($channelData);
        if ($channel->agent_id) {
            $this->informChatTransferToAgent($channelData);
        }
    }

    public function subscribe($events)
    {
        $events->listen(
            //ChatTerminate is the case when agent close chat
                'App\Events\ChatTerminate',
            'App\Listeners\MakeChatArchivedListener@handle'
        );
        $events->listen(
            //ChatTerminateByForce is the case when chat get closed forcefully due to unexpected offline
                'App\Events\ChatTerminateByForce',
            'App\Listeners\MakeChatArchivedListener@handle'
        );
        $events->listen(
            'App\Events\ChatTransferInternal',
            'App\Listeners\MakeChatArchivedListener@chatTransferInternal'
        );
        $events->listen(
            'App\Events\ChatTransferExternal',
            'App\Listeners\MakeChatArchivedListener@chatTransferExternal'
        );
    }

    private function markChannelTransferred($channelId)
    {
        $channel                 = ChatChannel::find($channelId);
        $channel->status         = self::CHANNEL_STATUS_TRANSFERED;
        $channel->transferred_at = Carbon::now()->timestamp;
        $channel->save();
        return $channel;
    }

    private function informChatTransferToAgent($channelData)
    {
        $clientInfo = Client::details($channelData['client_id']);

        if (!empty($channelData['via_internal_transfer'])) {
            $channelType = config('config.NOTIFICATION_EVENT_KEYS.internal_chat_transfer');
        } else {
            $channelType = config('config.NOTIFICATION_EVENT_KEYS.external_chat_transfer');
        }

        $identifierPermission = false;
        if (checkIndentifierMaskPermission($channelData['agent_id'])) {
            $identifierPermission = true;
            $clientInfo['raw_info'][$channelData['source_type']]['identifier'] =  mask($clientInfo['raw_info'][$channelData['source_type']]['identifier']);
        }

        /***********************Identifier Modification**************************/
        if ($channelData['source_type']=='whatsapp') {
            $client_display_label = checkOrganizationChatLabel($channelData['agent_id']);
            $clientInfo['name']   = client_display_name($client_display_label, $identifierPermission, $clientInfo['name'], $clientInfo['raw_info'][$channelData['source_type']]['name']);
            $clientInfo['raw_info'][$channelData['source_type']]['name'] = $identifierPermission ? mask($clientInfo['raw_info'][$channelData['source_type']]['name']) : $clientInfo['raw_info'][$channelData['source_type']]['name'];
        } else {
            $clientInfo['name'] = $identifierPermission ? mask($clientInfo['name']) : $clientInfo['name'];
        }
        /***********************Identifier Modification**************************/


        broadcast(new InformAgentPrivateChannel(
            [
                    'event'               => self::EVENT_NEW_CHAT,
                    'agent_id'            => $channelData['agent_id'],
                    'channel_agent_id'    => $channelData['agent_id'],
                    'group_id'            => $channelData['group_id'],
                    'id'                  => $channelData['id'],
                    'channel_name'        => $channelData['channel_name'],
                    'source_type'        =>  $channelData['source_type'],
                    'client_id'           => $channelData['client_id'],
                    'parent_id'           => $channelData['parent_id'],
                    'client_display_name' => $clientInfo['name'],
                    'client_raw_info'     => $clientInfo['raw_info'],
                    'has_history'         => $clientInfo['has_history'],
                    'unread_count'        => 1,
                    'recent_message'      => ['text' => $channelData['recent_message']],
                    'channel_type'        => $channelType
                        ]
        ))->toOthers();
        send_chat_notification($channelData['agent_id'], config('constants.CHAT_NOTIFICATION_EVENTS.TRANSFER'));
    }

    private function storeTransferMessage($channelData)
    {
        $comments = '';
        $transferredBy = '';

        if ($channelData['comments'] != "") {
            $transferredById  = $channelData['transferred_by'] ?? 0;
            $comments = $channelData['comments'];
            $transferredBy = User::find($transferredById)->name;
        }

        $transferMsg = [
                    'organization_id' => Group::find($channelData['group_id'])->organization_id,
                    'chat_channel_id' => $channelData['id'],
                    'client_id'       => $channelData['client_id'],
                    'user_id'         => $channelData['agent_id'],
                    'root_channel_id' => $channelData['root_channel_id'],
                    'identifier'      => Client::find($channelData['client_id'])->identifier ?? '',
                    'message'         => json_encode(['text' => $channelData['recent_message'], 'comments' => $comments, 'transferred_by' => $transferredBy]),
                    'recipient'       => ChatMessage::RECIPIENT_AGENT,
                    'message_type'    => self::TYPE_MESSAGE_TRANSFER,
                    'source_type'     => $channelData['source_type']
        ];

        ChatMessagesHistory::create($transferMsg);
    }

    private function handleInternalComments($channelId, $agentId)
    {
        $internalComment = InternalCommentChannel::where(['chat_channel_id' => $channelId, 'internal_agent_id' => $agentId])->first();
        if ($internalComment) {
            $internalComment->delete();
            broadcast(new InformAgentPrivateChannel([
                'event' => 'internal_comment_remove',
                'agent_id' => $agentId,
                'id'       => $channelId,
                ]))->toOthers();

        }
    }

    private function removeResponseDelayCacheKey($channelData)
    {
        $aTerminatedStatus = [
                                ChatChannel::CHANNEL_STATUS_TERMINATED_BY_AGENT,
                                ChatChannel::CHANNEL_STATUS_TERMINATED_BY_VISITOR

                            ];
        if (in_array($channelData['status'], $aTerminatedStatus)) {
            Cache::forget('rwf_'. $channelData['channel_name']);//Delete cache key  in case of chat terminate only which store agent response_within (seconds) time
        }

    }
}
