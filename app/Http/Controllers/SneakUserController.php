<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\SneakLog;
use App\User;
use App\Http\Requests\SneakUser\SneakRequest;

/**
 * Class used for sneak in by admin.
 */
class SneakUserController extends BaseController
{

    /**
     * Function used to sneak in by admin.
     *
     * @param Request $request
     * @return View
     */
    public function sneakIn(SneakRequest $request)
    {
        try{
            $request_params = $request->all();
            $user_id = $request_params['user_id'];
            Session::forget('is_sneak_return');
            //the user who is sneaking
            Session::put('sneak_in', Auth::id());
            //adding log for sneaking
            $log = SneakLog::addLog(Auth::id(), $user_id);
            //remove parent user details
            User::removeUserSession(Auth::id());
            //for saving current sneaking
            Session::put('sneak_id', $log->id);
            $redirect_page = $this->processSneakIn($user_id);
            return $this->successResponse(__('message.sneak_success'), $redirect_page);
        } catch(\Exception $exception) {
            return $this->failResponse(default_trans(Session::get('userOrganizationId').'/user_list.fail_messages.something_wrong', __('default/user_list.fail_messages.something_wrong')));
        }
    }

    /**
     * Function used to rever back to admin.
     *
     * @return View
     */
    public function returnSneak()
    {
        try {
            //flag to check whether sneak is return back.
            Session::put('is_sneak_return', true);
            
            //logged out user whose account was sneaked
            Auth::logout();
            //the user who has sneaked in.
            $parent_id = Session::pull('sneak_in');
            $redirect_page = $this->processSneakIn($parent_id);
            SneakLog::updateLog($parent_id);
            Session::forget('sneak_in');
            return redirect($redirect_page);
        } catch(\Exception $exception) {
            log_exception($exception);
        }
    }

    /**
     * Function to process sneak in either by admin or agent.
     *
     * @param integer $user_id
     */
    private function processSneakIn($user_id)
    {
        Auth::loginUsingId($user_id);
        $user = Auth::user();
        $user->user_session = session()->getId();
        $user->save();
        return get_rerdirect_page(Auth::user());
    }
    
    /**
     * Functon to check whether admin can sneak in the user account.
     * 
     * @param Request $request
     */
    public function checkSneak(SneakRequest $request)
    {
        try {
            $request_params = $request->all();
            $user_id = $request_params['user_id'];
            $is_login = User::checkUserLogIn($user_id);
            if (!empty($is_login)) {
                return $this->failResponse(__('message.user_already_login'));
            } else {
                return $this->successResponse(__('message.can_sneak_in'));
            }
        } catch(\Exception $exception) {
            return $this->exceptionRespnse($exception);
        }
    }
}