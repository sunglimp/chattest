<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Repositories\OrganizationRepository;
use Closure;

class OrganizationTokenAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(($request->has('access_token') and $token = $request->get('access_token') ) or $token = $request->bearerToken())
        {

            $request->merge(['access_token' => trim($token)]);
            $organization = new OrganizationRepository(new Organization());
            if($organization->findBySurboUniqueKey(trim($token)))
            {
                return $next($request);
            }
        }

        return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

    }
}
