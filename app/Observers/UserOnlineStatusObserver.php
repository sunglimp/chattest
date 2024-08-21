<?php

namespace App\Observers;

use App\Models\UserOnlineStatus;
use App\Events\UserOnlineStatusChanged;

class UserOnlineStatusObserver
{
    /**
     * Handle the user online status "saved" event.
     *
     * @param  \App\UserOnlineStatus  $userOnlineStatus
     * @return void
     */
    public function saved(UserOnlineStatus $userOnlineStatus)
    {
        dispatch(new UserOnlineStatusChanged($userOnlineStatus->user_id, $userOnlineStatus->status));
    }
}
