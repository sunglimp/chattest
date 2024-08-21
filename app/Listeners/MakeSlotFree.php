<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Redis;
use App\Events\InformAgentPrivateChannel;
use App\Agent;
use App\User;
use App\Models\Client;
use App\Models\ChatMessagesHistory;
use Cache;
use DB;
use Carbon\Carbon;
use App\Models\PermissionSetting;
use App\Models\Group;

class MakeSlotFree
{

    const CHANNEL_STATUS_UNPICKED = 1;


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {

        try {

            if (!empty($event->viaInternalTransfer)) {
            // If chat was creatd due to internal transfer then no need to minus number of chats in cache
                return;

            }

            if (empty(User::find($event->userId)->online_status)) {
                //If user is offline, don't assign new chat
                $this->minusNumOfChats($event->userId);
                return;
            }
            DB::beginTransaction();
             //If a waiting channel is found, then assigning it to agent otherwise incrementing slots
            $waitingChannel = DB::table('chat_channels')->whereIn('group_id', Agent::groupIds($event->userId))->whereNull('agent_id')->where('status', self::CHANNEL_STATUS_UNPICKED)->orderby('created_at')->lockForUpdate()->first();
            if ($waitingChannel) {
                DB::table('chat_channels')->where('id', $waitingChannel->id)->update(['agent_id' => $event->userId, 'agent_assigned_at' => Carbon::now()->timestamp, 'updated_at' => Carbon::now()->timestamp]);
                DB::table('chat_messages_history')->where('chat_channel_id', $waitingChannel->id)->update(['user_id' => $event->userId]);

            } else {
                $this->minusNumOfChats($event->userId);
            }

            // Identifier mask Permission check
            $identifierMaskPermission = checkIndentifierMaskPermission($event->userId);

            DB::commit();
            if ($waitingChannel) {
                $recentMsg=  ChatMessagesHistory::recentMessagePlusUnreadCount($waitingChannel->id);
                $clientInfo = Client::details($waitingChannel->client_id);

                // Masking Identifier based on condition
                $clientInfo['raw_info'][$waitingChannel->source_type]['identifier'] = $identifierMaskPermission ? mask($clientInfo['raw_info'][$waitingChannel->source_type]['identifier']) : $clientInfo['raw_info'][$waitingChannel->source_type]['identifier'];
                /***********************Identifier Modification**************************/
                if ($waitingChannel->source_type=='whatsapp') {
                    $client_display_label = checkOrganizationChatLabel($event->userId);
                    $clientInfo['name'] = client_display_name($client_display_label, $identifierMaskPermission, $clientInfo['name'], $clientInfo['raw_info'][$waitingChannel->source_type]['name']);
                    $clientInfo['raw_info'][$waitingChannel->source_type]['name'] = $identifierMaskPermission ? mask($clientInfo['raw_info'][$waitingChannel->source_type]['name']) : $clientInfo['raw_info'][$waitingChannel->source_type]['name'];
                } else {
                    $clientInfo['name'] = $identifierMaskPermission ? mask($clientInfo['name']) : $clientInfo['name'];
                }
                /***********************Identifier Modification**************************/

                broadcast(new InformAgentPrivateChannel(
                    [
                        'event' => 'new_chat',
                        'agent_id' => $event->userId,
                        'group_id' => $waitingChannel->group_id,
                        'channel_agent_id' => $event->userId,
                        'id' => $waitingChannel->id,
                        'channel_name' => $waitingChannel->channel_name,
                        'client_id' => $waitingChannel->client_id,
                        'client_display_name' => $clientInfo['name'],
                        'client_raw_info'     => $clientInfo['raw_info'],
                        'unread_count'        => $recentMsg['unread_count'],
                        'recent_message'      =>  $recentMsg['recent_message'],
                        'channel_type'        => config('config.NOTIFICATION_EVENT_KEYS.new_chat'),
                        'source_type'           => $waitingChannel->source_type
                    ]
                ))->toOthers();
                send_chat_notification($event->userId, config('constants.CHAT_NOTIFICATION_EVENTS.NEW_CHAT'));
                queue_chat_count($waitingChannel->group_id);
            }

        } catch (\Exception $exception) {
            DB::rollBack();
            \Log::error($exception->getMessage());
        }

    }

    private function minusNumOfChats($userId)
    {
        if (Cache::get('chats_' . $userId) > 0) {
            Cache::decrement('chats_' . $userId);
        }
    }
}