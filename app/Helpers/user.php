<?php

use App\Models\Role;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;

/**
 * Agent Helpers
 */
if (!function_exists('get_direct_reportees')) {


    /**
     * @todo not being used need to remove
     * @param int $reportedId
     * @return array
     */
    /**
     * @todo not being used need to remove
     * @param int $reportedId
     * @return array
     */
    function get_direct_reportees(int $reportedId, $isArray = false)
    {
        $users= User::where('id', $reportedId)->with('child')->first()->toArray();
        
        if ($isArray == true) {
            $directReportess = ($users['child']);
            return array_column($directReportess, 'id');
        }
        
        return $users;
    }

}

if (!function_exists('get_reporters')) {

    /**
     *
     * @param int $organizationId
     * @param int $roleId
     * @return array
     */
    function get_reporters(int $organizationId, int $roleId = 5)
    {
        $parents=User::where('organization_id', $organizationId)
            ->where('role_id', '<', $roleId)
            ->where('role_id', '!=', config('constants.user.role.super_admin'))
            ->get();

        return $parents;
    }

}

if (!function_exists('get_all_users')) {

    /**
     *
     * @param int $organizationId
     * @param int $roleId
     * @return array
     */
    function get_all_users(int $organizationId)
    {
        return User::where(['organization_id' => $organizationId, 'status' => User::STATUS_ACTIVE])
            ->pluck('name', 'id');
    }

}

if (!function_exists('get_children')) {

    /**
     *
     * @param int $userId
     * @return array
     * @todo should be changed use recurssion
     */
    function get_children(int $userId, $isOnline = true)
    {
        $allChilds= User::where('id', $userId)->with('child.child.child.child')->first();
        $allChildIds=[];
        
        if (isset($allChilds->child) && !empty($allChilds->child)) {
            //user have any number of child on same hirarchi
            foreach ($allChilds->child as $keyL1 => $valueL1) {
                if ($isOnline? $valueL1->online_status==1 :true) {
                    array_push($allChildIds, $valueL1->id);
                }
                    
                if (isset($valueL1->child) && !empty($valueL1->child)) {
                    foreach ($valueL1->child as $key2 => $valueL2) {
                        if ($isOnline? $valueL2->online_status==1 :true) {
                            array_push($allChildIds, $valueL2->id);
                        }
                        if (isset($valueL2->child) && !empty($valueL2->child)) {
                            foreach ($valueL2->child as $keyL3 => $valueL3) {
                                if ($isOnline ? $valueL3->online_status==1 :true) {
                                    array_push($allChildIds, $valueL3->id);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return  $allChildIds;
    }
    
}
    
    if (!function_exists('get_children_with_self')) {

    /**
     *
     * @param int $userId
     * @return array
     */
        function get_children_with_self(int $userId)
        {
            $allChilds= User::where('id', $userId)->with('child.child.child.child')->first();
            $allChildIds=[];
            array_push($allChildIds, $userId);
            if (isset($allChilds->child) && !empty($allChilds->child)) {
                //user have any number of child on same hirarchi
                foreach ($allChilds->child as $keyL1 => $valueL1) {
                    array_push($allChildIds, $valueL1->id);
                    if (isset($valueL1->child) && !empty($valueL1->child)) {
                        foreach ($valueL1->child as $key2 => $valueL2) {
                            array_push($allChildIds, $valueL2->id);
                            if (isset($valueL2->child) && !empty($valueL2->child)) {
                                foreach ($valueL2->child as $keyL3 => $valueL3) {
                                    array_push($allChildIds, $valueL3->id);
                                }
                            }
                        }
                    }
                }
            }
        
            return  $allChildIds;
        }
    }
    
    if (!function_exists('get_user_api_token')) {
        
        function get_user_api_token()
        {
            $apiToken = str_random(60);
            return $apiToken;
        }

    }
    
    
    if (!function_exists('get_rerdirect_page')) {
        /*
         *Function to get redirect page depending on role of user.
         * 
         */
        function get_rerdirect_page($user)
        {
            if (!Gate::allows('superadmin')) {
                $permission = Permission::find(config('constants.PERMISSION.DASHBOARD-ACCESS'));
                $isDashBoardAllowed = $user->can('check', $permission);
                if ($isDashBoardAllowed === true) {
                    $dashboardPage = '/';
                } else {
                    $dashboardPage = '/chat';
                }
            } else {
                $dashboardPage = '/';
            }
            
            switch ($user->role_id) {
                case config('constants.user.role.super_admin'):
                    return '/organization';
                    break;
                    
                case config('constants.user.role.admin'):
                    return '/permission';
                    break;
                case config('constants.user.role.manager'):
                case config('constants.user.role.team_lead'):
                case config('constants.user.role.associate'):
                    return $dashboardPage;
                    break;
                default:
                    return '/';
            }
        }
        
    }

    if (!function_exists('admin_accessing_user_dashboard')) {
        
        function admin_accessing_user_dashboard($loggedInUserId, $agentId)
        {
            $access = false;
            $loggedInUser = User::find($loggedInUserId);
            if ($loggedInUser->role_id == config('constants.user.role.admin')) {
                $user = User::find($agentId);
                $access = $user->organization_id == $loggedInUser->organization_id ? true : false;
            }
            return $access;
        }

    }
    
    
    

