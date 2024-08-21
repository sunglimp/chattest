<?php

namespace App\Http\Controllers;

use App\Exports\DashboardExport;
use App\Models\Summary;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Gate;
use App\Models\PermissionSetting;
use App\Models\Organization;

class DashboardController extends BaseController
{
    public function __construct()
    {
       // $this->middleware('can:not-superadmin');
    }
    
    /**
     * Function to show dashboard view.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function index(Request $request)
    {
        $loggedInUserId = $organization_id = '';
        $organization = $data = [];

        if (Gate::allows('not-superadmin')) {
            $loggedInUserId = Auth::id();
        } else {
            $organization = Organization::getOrganizationListWithUsers();

            if(empty($organization)) {
                return view('dashboard.super-admin-dashboard');
            }
            $organization_id = $request->organization_id ?? $organization[0]->id;
            $adminUser = User::getAdminUser($organization_id);

            if(empty($adminUser)) {
               return view('dashboard.super-admin-dashboard');  
            }
            $loggedInUserId = $adminUser->id ?? '';
        }
        $directReportess = get_direct_reportees($loggedInUserId, true);

        if (!empty($request->agentIds) &&  (!($request->agentIds == 'team' || $request->agentIds == $loggedInUserId || in_array($request->agentIds, $directReportess) || admin_accessing_user_dashboard($loggedInUserId, $request->agentIds)))) {
            return view('access-not-allowed');
        }
        
        $data = Summary::getDashBoardData($request, $loggedInUserId);

        return view('dashboard.index', compact('data', 'organization', 'organization_id'));
    }
    
    /**
     * Function for get dashbaord data through AJAX
     * 
     * @param Request $request
     * @return type
     */
    public function getDashboardData(Request $request)
    {

        $organization = Organization::active()->orderBy('created_at', 'desc')->get();
        $languageClass = '';
        $marginClass = 'margin-right-1';
        
        if (Auth::user()->role_id !=1 && Auth::user()->language =="ar"){
            $languageClass = 'arabic';
            $marginClass = 'margin-left-1';
        }
        
        if (Gate::allows('not-superadmin')) {
            $organizationId = Auth::user()->organization_id;
            $userId = Auth::id();
        }else {
            $organizationId = $request->organization_id;
            $adminUser = User::getAdminUser($organizationId);
            $userId = $adminUser->id ?? '';
        }
        
        if ($userId=='') {
            return $this->failResponse('Fail');
        }
        
        $data = Summary::getDashBoardData($request, $userId);
        $organization_id = $organizationId; //Avoid the conflict with view composer organizationId
        
        $response = view('dashboard.data', compact('data', 'organization', 'languageClass', 'marginClass', 'organization_id'))->render();
        if (!empty($data)) {
            return $this->successResponse('Success', $response);
        } else {
            return $this->failResponse('Fail');
        }
    }

    /**
     * Function to get data for highchart.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartData(Request $request)
    {
        try {
            $requestParams = $request->all();
            $organizationId =   $requestParams['organization_id'] ?? Auth::user()->organization_id;
            $startDate = $requestParams['startDate'];
            $endDate = $requestParams['endDate'];
            $parameter = $requestParams['parameter'];
            $agentId = $requestParams['agentId'];

            $data = $this->getData($startDate, $endDate, $parameter, $agentId, $organizationId);
            if (!empty($data)) {
                return $this->successResponse('Success', $data);
            } else {
                $this->failResponse('Fail');
            }
        } catch (\Excpetion $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    /**
     * Function to get data.
     *
     * @param string $days
     * @param string $parameter
     * @return NULL[][]|\Illuminate\Http\JsonResponse
     */
    private function getData($startDate, $endDate, $parameter, $agentId, $organizationId='')
    {
        try {
            if (Gate::allows('not-superadmin')) {
                $organizationId = Auth::user()->organization_id;
                $loggedInUserId = Auth::id();
            } else {
                $organizationId = $organizationId;
                $adminUser = User::getAdminUser($organizationId);
                $loggedInUserId = $adminUser->id ?? Auth::id();
            }
            
            if (empty($agentId) || $agentId == 'team') {
                $agents = get_children($loggedInUserId, false);
                $agents = !empty($agents) ? $agents: Auth::id();
                if (Gate::allows('manager') || Gate::allows('teamlead')) {
                    if (is_array($agents)) {
                        array_push($agents, $loggedInUserId);
                    }
                }
            } else {
                $agents = $agentId;
            }
          
            switch ($parameter) {
                case 'chat_count':
                    $data = Summary::getSummaryChartData('count_chat', $startDate, $endDate, $organizationId, $agents);
                    break;
                case 'termination_chat':
                    $data = Summary::getChatTerminationData($startDate, $endDate, $organizationId, $agents);
                    break;
                case 'queued_chat':
                    $data = Summary::getChatQueuedData($startDate, $endDate, $organizationId, $agents);
                    break;
            }
            return $data;
        } catch (\Excpetion $exception) {
            return $this->exceptionRespnse($exception);
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new DashboardExport($request->all()), config('config.DASHBOARD_REPORT_NAME'));
    }
    public function checkOnline()
    {
        return \App\User::where('id', Auth::user()->id)->first()->toJson();
    }
       
}
