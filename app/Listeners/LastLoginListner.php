<?php

namespace App\Listeners;

use App\User;
use Illuminate\Auth\Events\Login;

class LastLoginListner
{
  
  /**
     * Handle the event.
     *
     * @param  LastLogin  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $event->user->last_login=\Carbon\Carbon::now()->timestamp;
        $event->user->save();
    }
}
