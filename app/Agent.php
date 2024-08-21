<?php

namespace App;

use App\User;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Cache;

class Agent extends User
{

    /*   
    
        Return userId to whom chat should be assigned now, will return null if no one found
    */
    public static function availableUser($groupId, $except = null)
    {
        $onlineUsers = Redis::smembers('group_' . $groupId);
        if (empty($onlineUsers)) {
            return null;
        }
        $cacheNoOfChatsKeys = [];
        //number of chats are stored in cache in format of key 'chats_{userId}'
        foreach ($onlineUsers as $val) {
            $cacheNoOfChatsKeys[] = 'chats_' . $val;

        }
        $chats = Cache::many($cacheNoOfChatsKeys);
        asort($chats);
        foreach ($chats as $keyUser => $valChat) {
            $userId =  str_replace('chats_', '', $keyUser);
            if (Cache::get('slots_' . $userId ) > $valChat && ($userId != $except)) {
                $availableUserKey = $userId;
                break;
            }
        }
        if (empty($availableUserKey)) {
            return null;
        } else {
            Cache::increment('chats_' . $availableUserKey);
            return $availableUserKey;
        }
    }

    

    /*
                

    */

    public static function displayName($id)
    {
        //@TODO- May be driven using cache later
        return User::withTrashed()->find($id)->name ?? '';

    }
    
    public static function groupIds($userId)
    {
        return UserGroup::where('user_id', $userId)->pluck('group_id')->all();
    }
    
    public static function autoTransferDelay($agentId)
    {
       $userPermissionSetting= User::find($agentId)->getPermissionSetting('auto-chat-transfer');
        if (count($userPermissionSetting)) {
            return Carbon::now()->addHour($userPermissionSetting['hour'])
                ->addMinutes($userPermissionSetting['minute'])
                ->addSecond($userPermissionSetting['second']);
        } else {
            return false;
        }
    }
}