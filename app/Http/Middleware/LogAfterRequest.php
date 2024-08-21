<?php

namespace App\Http\Middleware;

use Closure;
//use Illuminate\Support\Facades\Log;


class LogAfterRequest
{
    public function handle($request, \Closure  $next)
    {
        return $next($request);
    }

    public function terminate($request, $response)
    {
        info('API URL ' . $request->fullUrl());
        info('Requested Parameters');
        info(json_encode($request->all()));
        info('Response');
        info('Http Code: ' . $response->getStatusCode());
        info($response->getContent());
    }
}
