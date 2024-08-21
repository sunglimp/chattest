<?php

namespace App\Http\Middleware;

use Closure;
use App\Repositories\OrganizationRepository;
use App\Models\Organization;

/**
 * Middleware to authorize TMS request.
 *
 */
class TMSIntegrationMiddleware
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
        if (($request->has('access_token') and $token = $request->get('access_token') ) or $token = $request->bearerToken()) {
            $request->merge(['access_token' => trim($token)]);
            $organization = new OrganizationRepository(new Organization());
            if ($organization->findByTmsUniqueKey(trim($token))) {
                return $next($request);
            }
        }
        return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        return $next($request);
    }
}
