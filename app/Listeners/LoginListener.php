<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\LoginHistory;
use Carbon\Carbon;
use Jenssegers\Agent\Facades\Agent;
use Illuminate\Support\Facades\Session;
use App\Models\SneakLog;

class LoginListener
{
    private $isUserLogin = 1;

    /**
     * Handle the event.
     *
     * @param  LastLogin  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $user = $event->user;
        // Changes for Mobile Api Login
        $deviceId     =  $user->device_id ?? null;
        $deviceType   =  $user->device_type ?? 3;
        $deviceToken  =  $user->device_token ?? null;
        $deviceDetail =  empty($deviceId) ? Agent::browser() : Agent::device();
        unset($user->device_id, $user->device_type, $user->device_token);

        //Reentry or new login time will update here
        $user->last_login= Carbon::now()->timestamp;
        $user->is_login = $this->isUserLogin;
        $user->api_token = get_user_api_token();
        $user->save();

        //preventing admin to add login history again after sneak back
        if (!Session::has('is_sneak_return')) {
            //Get last entry of a user
            $historDetails = LoginHistory::where('user_id', $user->id)->latest('id')->first();

            $addHistory = (!empty($historDetails)) ? (($historDetails->logout_time!=null) ? true : false) : true ;
            //If the user not logout properly then exitsing login time will contitune
            if ($addHistory) {
                $data = ['user_id'=> $user->id , 'login_time'=> Carbon::now()->timestamp,'ip_detail'=>\Request::ip(),'device_detail'=>$deviceDetail, 'device_id'=> $deviceId, 'device_type'=> $deviceType, 'device_token'=> $deviceToken];
                // Insert into history table after login
                LoginHistory::create($data);
            }
        }
    }
}
