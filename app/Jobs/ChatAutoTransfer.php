<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ChatChannel;
use App\Models\ChatMessage;
use App\Models\ChatMessagesHistory;
use App\Events\InformAgentPrivateChannel;
use App\Events\ChatTransfer;
use App\Models\Client;
use App\Agent;
use App\User;
use Carbon\Carbon;
use App\Models\Group;
use Illuminate\Support\Facades\Redis;
use App\Http\Utilities\CommonHelper;

class ChatAutoTransfer implements ShouldQueue
{

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    const EVENT_CHAT_TRANSFER   = 'chat_transfer';
    const EVENT_NEW_CHAT        = 'new_chat';
    const CHAT_AUTO_TRANSFER    = 'Chat is transferred automatically';
    const TYPE_MESSAGE_TRANSFER = 'transfer';

    private $chatChannel;
    private $transferViaForceLogout;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ChatChannel $chatChannel, $transferViaForceLogout = 0)
    {
        $this->chatChannel  = $chatChannel;
        $this->transferViaForceLogout = $transferViaForceLogout;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        info($this->chatChannel);
        if ($this->chatChannel->status == ChatChannel::CHANNEL_STATUS_UNPICKED) {
            // Auto Transfer limit check in case of normal chat auto transfer
            if ($this->transferViaForceLogout == 0) {
                $transferChannelId  = ($this->chatChannel->root_channel_id) ? $this->chatChannel->root_channel_id : $this->chatChannel->id;
                $transferCountValue = Redis::get('channel_id_' . $transferChannelId);
                $organizationId     = Group::where('id', $this->chatChannel->group_id)->pluck('organization_id');
                if ($transferCountValue == CommonHelper::getOrganizationAutoTransfer($organizationId)) {
                    return;
                }
            }
            $agent_id = Agent::availableUser($this->chatChannel->group_id, $this->chatChannel->agent_id);
            if (empty($agent_id)) {
                if ($this->transferViaForceLogout) {
                    $this->chatChannel->agent_id          = null;
                    $this->chatChannel->agent_assigned_at = null;
                    $this->chatChannel->save();

                    //broadcast queue count
                    queue_chat_count($this->chatChannel->group_id);
                }
                $autoTransferDelay = ($this->chatChannel->agent_id != null) ? Agent::autoTransferDelay($this->chatChannel->agent_id) : Carbon::now()->addSecond(60);
                if ($autoTransferDelay) {
                    self::dispatch($this->chatChannel)
                            ->onQueue(config('chat.queues.auto_transfer'))
                            ->delay($autoTransferDelay);
                }
                return;
            }

            $this->chatChannel->status         = ChatChannel::CHANNEL_STATUS_TRANSFERED;
            $this->chatChannel->transferred_at = Carbon::now()->timestamp;
            $this->chatChannel->save();
            $channelData                       = [];
            $channelData['channel_name']       = $this->chatChannel->channel_name;
            $channelData['token']              = $this->chatChannel->token;
            $channelData['end_point']          = $this->chatChannel->end_point;
            $channelData['group_id']           = $this->chatChannel->group_id;
            $channelData['agent_id']           = $agent_id;
            $channelData['client_id']          = $this->chatChannel->client_id;
            $channelData['parent_id']          = $this->chatChannel->id;
            $channelData['source_type']        = $this->chatChannel->source_type;
            $channelData['root_channel_id']    = $this->chatChannel->root_channel_id ?? $this->chatChannel->id;
            $channel                           = ChatChannel::create($channelData);
            ChatMessage::transferMessagesToHistory($this->chatChannel->toArray());
            if ($channel->agent_id) {
                $channelData['agent_id'] = $channel->agent_id;
                $channelData['id']       = $channel->id;
                $this->informNewChatToAgent($channelData);
            }
            $this->storeAutoTransferMessage($channel);
            event(new ChatTransfer($this->chatChannel->agent_id, $this->chatChannel->id));
            //Inform to agent's private channel to remove the chat
            broadcast(new InformAgentPrivateChannel([
                'event'        => self::EVENT_CHAT_TRANSFER,
                'agent_id'     => $this->chatChannel->agent_id,
                'id'           => $this->chatChannel->id,
                'channel_name' => $this->chatChannel->channel_name,
            ]))->toOthers();

            // increase auto transfer count
            if ($this->transferViaForceLogout == 0) {
                $transferCount = $transferCountValue + 1;
                Redis::set("channel_id_" . $transferChannelId, $transferCount, 'EX', 24 * 60 * 60);
            }
        }
    }

    private function storeAutoTransferMessage(ChatChannel $channel)
    {
        $transferMsg = [
            'organization_id' => Group::find($channel->group_id)->organization_id,
            'chat_channel_id' => $channel->id,
            'client_id'       => $channel->client_id,
            'user_id'         => $channel->agent_id,
            'root_channel_id' => $channel->root_channel_id,
            'identifier'      => Client::find($channel->client_id)->identifier ?? '',
            'message'         => json_encode(['text' => self::CHAT_AUTO_TRANSFER]),
            'recipient'       => 'AGENT',
            'message_type'    => self::TYPE_MESSAGE_TRANSFER,
        ];
        ChatMessagesHistory::create($transferMsg);
    }

    private function informNewChatToAgent($channelData)
    {
        $clientInfo = Client::details($channelData['client_id']);

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
            'client_id'           => $channelData['client_id'],
            'parent_id'           => $channelData['parent_id'],
            'source_type'         => $channelData['source_type'],
            'client_display_name' => $clientInfo['name'],
            'client_raw_info'     => $clientInfo['raw_info'],
            'has_history'         => $clientInfo['has_history'],
            'unread_count'        => ChatMessagesHistory::unreadCountByRootChannel($channelData['root_channel_id'])['unread_count'] + 1,
            'recent_message'      => ['text' => self::CHAT_AUTO_TRANSFER],
            'channel_type'        => config('config.NOTIFICATION_EVENT_KEYS.automatic_chat_transfer')
                ]
        ))->toOthers();

        send_chat_notification($channelData['agent_id'], config('constants.CHAT_NOTIFICATION_EVENTS.TRANSFER'));
        queue_chat_count($channelData['group_id']);
    }

}
