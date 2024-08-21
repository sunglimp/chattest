<?php

namespace App\Widgets;

use \App\Models\Summary,
    \App\User,
    \Arrilot\Widgets\AbstractWidget;

use Illuminate\Support\Facades\Gate;
use \Illuminate\ {
    Http\Request,
    Support\Facades\Auth
};

class Slider extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];
    public $encryptParams = false;

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
            $agents = !empty($agents) ? $agents : $loggedInUserId;
            info($agents);
            if (Gate::allows('manager') || Gate::allows('teamlead')) {
                if(is_array($agents)) {
                    array_push($agents, $loggedInUserId);
                }
            }
        } else {
            $agents = $requestParams['agentIds'];
            $agents = explode(" ", $agents);
        }

        //$organizationWiseDataFlag Flag used to show some organization specific fields in dashboard widget
        $organizationWiseDataFlag = false;
        if(Gate::allows('all-admin') && (empty($requestParams['agentIds']) || strtolower($requestParams['agentIds']) =="team")) {
          $organizationWiseDataFlag = true;
        }
        $data = Summary::getSummaryData($requestParams, $organizationId, $agents, false, $organizationWiseDataFlag);
        $data->numberOfChats += (empty($data->outSessionMissedChats) ? 0 : $data->outSessionMissedChats) + (empty($data->outSessionTimeouts) ? 0 : $data->outSessionTimeouts ) + (empty($data->countOfflineQuery) ? 0 : $data->countOfflineQuery);
        return view('widgets.slider', [
            'config' => $this->config,
        ],compact('data','organizationWiseDataFlag'));
    }
}
