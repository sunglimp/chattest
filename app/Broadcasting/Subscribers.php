<?php

namespace App\Broadcasting;

use App\User;

class Subscribers
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\User  $user
     * @return array|bool
     */
    public function join(User $user, $agent)
    {
        /**
         * @todo Or condition has to fix on the basis of role
         */
        return ($user->id == $agent or $user->name='admin2');
    }
}
