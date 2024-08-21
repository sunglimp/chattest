<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\LoginHistory;
use DB;
use Illuminate\Support\Facades\Session;
use App\Models\SneakLog;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class LogoutListener
{
    private $isUserLogin = 0;

    /**
     * Handle the event.
     *
     * @param  LastLogin  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $user= $event->user;
        if ($user) {
            $this->logoutOperations($user);
            //to avoid log out of parent user while sneak logout
            if (!Session::has('is_sneak_return')) {
                // log out admin too while actual log out of agent
                $this->sneakParentLogout($user);
            }
        }
    }

    /**
     * Function to do operations before logout.
     *
     * @param Model $user
     */
    private function logoutOperations($user)
    {
        $user->is_login = $this->isUserLogin;
        $user->api_token = NULL;
        $user->save();

        // update the logout time
        LoginHistory::updateLogoutTime($user->id, $user->role_id);
    }

    /**
     * this function is used to log out of current user if parent user is not logged out.
     * this is not applicable while return back.
     *
     * @param Model $user
     */
    private function sneakParentLogout($user)
    {
        $is_sneak_in = SneakLog::getParentUser($user);
        if (! empty($is_sneak_in)) {
            $is_sneak_in_parent = $is_sneak_in->parent_id;
            $user = User::find($is_sneak_in_parent);
            Auth::setUser($user);
            
            // Sneak Parent logout
            Auth::logout();
            
            $sneak_in_user = $is_sneak_in->user_id;
            $sneak_in_user = User::find($is_sneak_in_parent);
            
            //Sneak user set to log out
            Auth::setUser($sneak_in_user);
        }
    }
}
