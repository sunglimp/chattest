<?php

namespace App\Observers;

use App\Agent;
use App\Models\ChatChannel;

use App\Jobs\ChatAutoTransfer;
use Carbon\Carbon;

/**
 * @author Ankit Vishwakarma <ankit.vishwakarma@vfirst.com>
 */
class ChatChannelObserver
{
    /**
     * Handle the chat channel "creating" event.
     *
     * @param  \App\ChatChannel  $chatChannel
     * @return void
     */
    public function creating(ChatChannel $chatChannel)
    {
        /**
         * 1. Check if there is any chat of same group which are not picked
         * 2. if found they are more important than the new chat
         * 3. send the new chat into queue
         * 4. assign agent to the channel once an agent free
         */
        $chatChannel->status = ChatChannel::CHANNEL_STATUS_UNPICKED;
        $chatChannel->channel_name = $chatChannel->channel_name
                ?? ChatChannel::CHANNEL_PREFIX . guid();
        $currentTime = Carbon::now()->timestamp;
        if ($chatChannel->agent_id) {
            //Case of transfer to already known agent
            $chatChannel->agent_assigned_at = $currentTime;
            return;
        }
        $chatInQueue = ChatChannel::where('group_id', $chatChannel->group_id)->waitingQueue()->exists();
        $chatChannel->agent_id = $chatInQueue ? null : ($chatChannel->agent_id
                ?? Agent::availableUser($chatChannel->group_id));
        $chatChannel->queued_at = empty($chatChannel->agent_id) ? $currentTime : null;
        $chatChannel->agent_assigned_at = empty($chatChannel->agent_id) ? null : $currentTime;
    }
    
    public function created(ChatChannel $chatChannel)
    {

        /**
         * if channel found the agent, fortunately. The agent is supposed to pick
         * the channel in given period of time
         */
        
        if (!empty($chatChannel->agent_id)) {
            $autoTransferDelay = Agent::autoTransferDelay($chatChannel->agent_id);
            if ($autoTransferDelay) {
                ChatAutoTransfer::dispatch($chatChannel)
                    ->onQueue(config('chat.queues.auto_transfer'))
                    ->delay($autoTransferDelay);
            }
        } else {
            $groupId = $chatChannel->group_id ?? null;
            queue_chat_count($groupId);
        }

    }

}
