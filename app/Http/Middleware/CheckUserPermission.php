<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Support\Facades\Gate;

class CheckUserPermission
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return
      mixed
     */
    public function handle($request, Closure $next, $permissionId)
    {
         $user = Auth::user();
 // check organization role pemission
        if (!Gate::allows('superadmin', Auth::User()->role_id)) {
           //$permission=( isset(Auth::User()->user_permission[$permissionId]) &&(Auth::User()->user_permission[$permissionId]==true))?1:0;
             $permission = \App\Http\Controllers\PermissionController::checkPermissions($user->organization_id, $user->role_id, $permissionId);
            if (empty($permission)) {
                if ($request->ajax()) {
                    return
                            response()->json(['status' =>
                                false,
                                'errors' =>
                                'User has not permission to access this resource']);
                } else {
                    return
                            abort(403, 'Unauthorized access');
                }
            }
        }
        return $next($request);
    }
}
