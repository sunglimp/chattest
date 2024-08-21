<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Support\Facades\Auth;

/**
 * @author Ankit Vishwakarma <ankit.vishwakarma@vfirst.com>
 *
 * Logout If No Referer Found
 * Need to click on the links available
 */
class LogoutIfNoReferer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check() && !$request->headers->has('referer')) {
            Auth::guard($guard)->logout();
            return redirect('/');
        }
        
        return $next($request);
    }
}
