<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Redis;
use App\Events\UserOnline;
use App\User;
use App\Models\UserGroup;
use Cache;

class MakeOnlineInCache
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
    public function handle(UserOnline $event)
    {
        $user = User::find($event->userId);
        //Make user online in all related groups
        $userGroups = UserGroup::where('user_id', $event->userId)->get();
        foreach ($userGroups as $group) {
            //group_{ID} key format, set online
            Redis::sadd('group_' . $group->group_id, $event->userId);
        }
        Cache::forever('slots_' . $event->userId, $user->no_of_chats);
    }
}
