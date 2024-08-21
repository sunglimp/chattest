<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Permission;
use App\Models\OrganizationRolePermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    public function check(User $user, Permission $permission)
    {
        if (Auth::check() && !Gate::allows('superadmin')) {
            $organizationId = $user->organization_id;
            $roleId = $user->role_id;
            $permissionId = $permission->id;
            $organizationPermission = OrganizationRolePermission::getOrganizationRolePermission(
                $organizationId,
                $permissionId,
                $roleId
            );
            if (!empty($organizationPermission)) {
                $userPermission = '';
                if (isset($user->user_permission[$permissionId])) {
                    $userPermission = $user->user_permission[$permissionId];
                }
                return !empty($userPermission);
            }
        } else {
            return true;
        }
    }
}
