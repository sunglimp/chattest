<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Collection;
use App\User;
use DB;
use App\Models\PermissionSetting;

class OrganizationRolePermission extends Model
{

    // Get Super Admin Organization permission data
    public static function getSuperAdminOrganizationPermission($organizationId)
    {
        $organizationPermissions = self::getOrganizationPermission($organizationId);
        $masterPermission        = Permission::all();
        $userRoles               = Role::userRole(config('constants.user.role.super_admin'))->get();
        $userCountByRole         = User::getUserCountByRole($organizationId);
        return array(
            'permission_list'         => $masterPermission,
            'organization_permission' => $organizationPermissions,
            'user_roles'              => $userRoles,
            'user_counts'              => $userCountByRole
        );
    }

    // Get Admin Organization permission data
    public static function getAdminOrganizationPermission($organizationId)
    {
        $organizationPermissions = self::getOrganizationPermission($organizationId);
        $masterPermission        = Permission::wherehas('adminpermission', function ($query) use ($organizationId) {
                    $query->where('role_id', config('constants.user.role.admin'))
                            ->where('organization_id', $organizationId);
        })->get();
        $userRoles = Role::wherehas('rolepermission', function ($query) use ($organizationId) {
                    $query->where('role_id', '!=', config('constants.user.role.admin'))
                            ->where('organization_id', $organizationId)
                            ->where('permission_id', 1);
        })->get();
        $userCountByRole         = User::getUserCountByRole($organizationId);
        return array(
            'permission_list'         => $masterPermission,
            'organization_permission' => $organizationPermissions,
            'user_roles'              => $userRoles,
            'user_counts'              => $userCountByRole
        );
    }

    private static function getOrganizationPermission($organizationId)
    {

        $organizationPermissions = OrganizationRolePermission::where('organization_id', $organizationId)->get()->toArray();

        return $organizationPermissions;
    }

    public static function getOrganizationRolePermission(int $organizationId, int $permissionId, int $roleId): array
    {
        return self::where('organization_id', $organizationId)
                        ->where('permission_id', $permissionId)
                        ->where('role_id', $roleId)
                        ->get()->toArray();
    }
    
    /**
     * 
     * @param type $organizationId
     * @return type
     */
    public function getOrganizationChatTimeoutPermission($organizationId)
    {
        return self::where('permission_id', config('constants.PERMISSION.TIMEOUT'))
            ->where('organization_id', $organizationId)->first();
    }


    /**
     * Function for get organization chat timeout value in seconds
     * 
     * @param type $organizationId
     * @return mixed
     */
    public function getOrganizationChatTimeoutValue($organizationId)
    {
        $expireTime = 'false';
        $organizationPermission = self::getOrganizationChatTimeoutPermission($organizationId);
        if ($organizationPermission) {
            $expireTime = (new PermissionSetting)->getChatTimeoutSettings($organizationId);
            $expireTime = ($expireTime!='') ? (int)$expireTime : config('chat.chat_default_expire_time')*60;
        }
        return $expireTime;
    }
    
    public static function getUserSideBarPermissions($user, $permissionIds){
        
     $orgPermissionList =  OrganizationRolePermission::where('organization_id', $user->organization_id)
                ->where('role_id', $user->role_id)
                ->whereIn('permission_id', $permissionIds)
                ->pluck('permission_id');
            $userPermissionList = $user->user_permission;
            $permissionMappingArray = config('constants.SIDEBAR_PERMISSION_MAPPING_WITH_LANGUAGE');
            $sidebar = [];
            foreach ($orgPermissionList as $value) {
                //Priotity for organization permission, in the case of true then user permission will check
                if ($userPermissionList) {
                    if (in_array($value, $userPermissionList)) {
                        if (isset($userPermissionList[$value]) && $userPermissionList[$value] == true) {
                           if($value == config('constants.PERMISSION.TMS-KEY')){
                            $sidebar[config('constants.FIXED_SIDEBAR_CUSTOM_PERMISSION_IDS.LEAD_ENQUIRE')] = default_trans($user->organization_id.'/sidebar.ui_elements_messages.lead_enquire', __('default/sidebar.ui_elements_messages.lead_enquire'));   
                           }
                            $sidebar[$value] = default_trans($user->organization_id.'/sidebar.ui_elements_messages.'.$permissionMappingArray[$value], __('default/sidebar.ui_elements_messages.'.$permissionMappingArray[$value]));
                           }
                    }
                }
            } 
            return $sidebar;
            
    }
}
