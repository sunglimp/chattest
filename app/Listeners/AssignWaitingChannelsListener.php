<?php

namespace App\Listeners;

use App\Events\UserOnline;
use App\Events\InformAgentPrivateChannel;

use App\Models\ChatMessagesHistory;

use App\User;
use App\Agent;
use DB;
use Cache;
use Carbon\Carbon;

class AssignWaitingChannelsListener
{

    
    public function handle(UserOnline $event)
    {
        $noOfChatsAllowed = User::find($event->userId)->no_of_chats - Cache::get('chats_' . $event->userId);
        
        try {
            DB::beginTransaction();
            $waitingChannels = DB::table('chat_channels')->whereIn('group_id', Agent::groupIds($event->userId))->whereNull('agent_id')->where('status',  config('constants.CHAT_STATUS.UNPICKED'))->orderby('created_at')->limit($noOfChatsAllowed)->lockForUpdate()->get();
            $count = $waitingChannels->count();
            $assignedChannelIds = [];
            foreach ($waitingChannels as $channel) {
                $assignedChannelIds[] = $channel->id;
            }
            $currentTime = Carbon::now()->timestamp;
            if (!empty($assignedChannelIds)) {
                DB::table('chat_channels')->whereIn('id', $assignedChannelIds)->update(['agent_id' => $event->userId, 'agent_assigned_at' => $currentTime, 'updated_at' => $currentTime]);
            } 
            Cache::increment('chats_' . $event->userId, $count);
            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
            \Log::error($exception->getMessage());
        }
        
        if (!empty($assignedChannelIds)) {
            ChatMessagesHistory::whereIn('chat_channel_id', $assignedChannelIds)->update(['user_id' => $event->userId]);
            
            broadcast(new InformAgentPrivateChannel([
                'event' => 'bulk_channels_assign',
                'agent_id' => $event->userId,
                'channel_agent_id' => $event->userId,
            ]))->toOthers();
        }
        //broadcat queue count
        $groupIds = Agent::groupIds($event->userId);
        $groupId = $groupIds[0] ?? null;
        queue_chat_count($groupId);
    }
}
