<?php

namespace App\Broadcasting;

use App\User;
use App\Models\ChatChannel;

class Supervision
{
    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\User  $user
     * @return array|bool
     */
    public function join(User $user, $channel)
    {
        //Logged in user is already in the channel
        return ChatChannel::where(['agent_id' => $user->id, 'channel_name' => $channel])->exists() || $user->name = 'agent1';
        //Logged in user is supervisor
    }
}
