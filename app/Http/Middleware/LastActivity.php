<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class LastActivity
{
    public function handle($request, Closure $next)            
    {   
        $key = 'last_activity_' . Auth::id();
        $value = \Carbon\Carbon::now()->timestamp;     
        Redis::set($key, $value);
        
        return $next($request);
    }
}
