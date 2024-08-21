<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
      |--------------------------------------------------------------------------
      | Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles authenticating users for the application and
      | redirecting them to your home screen. The controller uses a trait
      | to conveniently provide its functionality to your applications.
      |
     */

    use AuthenticatesUsers {
        logout as authenticateLogout;
    }

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';
    private $isUserLogin = 0;
    private $isOrganizationActive = 1;
    private $isUserActive = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
    public function authenticated(Request $request, $user)
    {
    
        $this->redirectTo = get_rerdirect_page($user);
        
        $user  = auth()->user();
        $user->user_session = session()->getId();
        $user->save();
        
       $this->setUserOrganizationId($user);
        
        return redirect($this->redirectTo);
    }

    /**
     * Redirect to url after logout.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $this->authenticateLogout($request);
        if ($request->ajax()) {
            return response()->json(['Session_error'=>'Session Expired'], 401);
        }
        return redirect()->route('login');
    }

    protected function attemptLogin(Request $request)
    {
        if ($this->guard()->attempt([
                'email' => $request->input('email'),
                'password' => $request->input('password'),
                'status' => $this->isUserActive,
                'is_login'=> $this->isUserLogin
            ], $request->filled('remember'))) {
            $user = auth()->user();
            $return = true;
            if ($user->role_id!= config('constants.user.role.super_admin') && $user->organization->status != $this->isOrganizationActive) {
                $return = false;
            }
            return $return;
        }
    }
    
    /**
     * Function to set user locale.
     * 
     * @param Model $user
     */
    private function setUserOrganizationId($user)
    {
        if (Gate::allows('superadmin')) {
            $organizationId = 0;
        } else {
            $organizationId = Auth::user()->organization_id;
        }
        Session::put('userOrganizationId', $organizationId);
    }
}
