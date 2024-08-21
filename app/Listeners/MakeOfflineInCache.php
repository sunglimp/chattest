<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Redis;
use App\Events\UserOffline;
use App\Models\UserGroup;

use Cache;

class MakeOfflineInCache
{
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
    public function handle(UserOffline $event)
    {
        $userGroups = UserGroup::where('user_id', $event->userId)->get();
        foreach ($userGroups as $group) {
            Redis::srem('group_' . $group->group_id, $event->userId);
        }
        Cache::forget('slots_' . $event->userId);
        if ($event->makeChatTerminated) {
            Cache::forget('chats_' . $event->userId);
        }
    }
}
