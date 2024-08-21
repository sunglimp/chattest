<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\User;

class DashboardExport implements WithMultipleSheets, ShouldAutoSize
{
    use Exportable;
    
    protected $startDate;
    protected $endDate;
    protected $agentIds;
    
    protected $request;
    //
    public function __construct($request)
    {
        $this->request= $request;
    }

    /**
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $userId = Auth::id();
        $startDate = date(config('settings.mysql_date_format'), strtotime($this->request['startDate'] ?? date('Y-m-d'))) ?? date('Y-m-d');
        $endDate = date(config('settings.mysql_date_format'), strtotime($this->request['endDate'] ?? date('Y-m-d'))) ?? date('Y-m-d');
        $agents = $this->request['agentIds'];
        $organizationId = $this->request['organizationId'] ?? '';
        if ((empty($agents) || $agents == 'team') && (Gate::allows('manager') || Gate::allows('teamlead') || Gate::allows('all-admin'))) {
            $organizationWiseDataFlag = false;
            if(Gate::allows('all-admin') && (empty($agents) || $agents == 'team')) {
                $organizationWiseDataFlag = true;
            }
            
            if (Gate::allows('not-superadmin')) {
               $userId = Auth::id();
            } else {
               //In the case of superadmin set user id as admin user id of selected organization  
               $adminUser = User::getAdminUser($organizationId);
               $userId = $adminUser->id ?? Auth::id();
            }
            
            $agents = get_children($userId, false);
            $agents = !empty($agents) ? $agents : Auth::id();
           
            if (is_array($agents) && (Gate::allows('manager') || Gate::allows('teamlead'))) {
                array_push($agents, Auth::id());
            }
            $sheets[] = new TeamDetailsSummaries($startDate, $endDate, $agents, $organizationId, $organizationWiseDataFlag);
            $sheets[] = new TeamDetailsChatCount($startDate, $endDate, $agents, $organizationId);
            $sheets[] = new TeamDetailsChatDuration($startDate, $endDate, $agents, $organizationId);
        } else {
            if (empty($agents)) {
                $agents = Auth::id();
            }
            $sheets[] = new TeamDetailsSummaries($startDate, $endDate, $agents);
        }
        return $sheets;
    }
}
