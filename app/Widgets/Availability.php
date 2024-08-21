<?php

namespace App\Widgets;

use Arrilot\Widgets\AbstractWidget;
use App\Models\Summary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\User;

class Availability extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run(Request $request)
    {
        $requestParams = $request->all();
        
        if (Gate::allows('not-superadmin')) {
            $organizationId = Auth::user()->organization_id;
            $loggedInUserId = Auth::id();
        } else {
            $organizationId = $requestParams['organization_id'] ?? Auth::user()->organization_id;
            $adminUser = User::getAdminUser($organizationId);
            $loggedInUserId = $adminUser->id ?? Auth::id();
        }
        
        if (empty($requestParams['agentIds']) || strtolower($requestParams['agentIds']) =="team") {
            $agents = get_children($loggedInUserId, false);
            $agents = !empty($agents) ? $agents : Auth::id();
            if (Gate::allows('manager') || Gate::allows('teamlead')) {
                if(is_array($agents)) {
                    array_push($agents, $loggedInUserId);
                }
            }
        } else {
            $agents = $requestParams['agentIds'];
            $agents = explode(" ", $agents);
        }
        
        $data = Summary::getAvailabilityData($requestParams, $organizationId, $agents);
        
        return view('widgets.availability', [
            'config' => $this->config,
        ], compact('data'));
    }
}
