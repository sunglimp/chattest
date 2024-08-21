<?php

namespace App\Http\ViewComposers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use App\Models\OrganizationRolePermission;
use App\Models\User;

class SidebarComposer
{
    /**
     * The user repository implementation.
     *
     * @var UserRepository
     */
    protected $users;
    public $permissions = [];

    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct()
    {
        if (!Gate::allows('superadmin', Auth::User()->role_id)) {
            $organizationId = Auth::User()->organization_id;
            $roleId = Auth::User()->role_id;
            $orgPermissionList =  OrganizationRolePermission::where('organization_id', $organizationId)
                ->where('role_id', $roleId)
                ->get(['permission_id'])
                ->toArray();
            $userPermissionList = Auth::User()->user_permission;
            foreach ($orgPermissionList as $value) {
                $this->permissions[$value['permission_id']] = false;
                //Priotity for organization permission, in the case of true then user permission will check
                if ($userPermissionList) {
                    if (in_array($value['permission_id'], $userPermissionList)) {
                        if (isset($userPermissionList[$value['permission_id']]) && $userPermissionList[$value['permission_id']] == true) {
                            $this->permissions[$value['permission_id']] = true;
                        }
                    }
                }
            }
        } else {
            $permissionList = \App\Models\Permission::get(['id'])
                ->toArray();
            foreach ($permissionList as $value) {
                if (!in_array($value['id'], config('config.SUPERADMIN_RESTRICTED_PERMISSIONS'))) {
                    $this->permissions[$value['id']] = true;
                } else {
                    $this->permissions[$value['id']] = false;
                }
            }
        }
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('permissions', $this->permissions);
    }
}
