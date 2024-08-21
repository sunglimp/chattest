<?php

use Illuminate\Database\Seeder;

use App\Models\OrganizationRolePermission;
use App\Models\Permission;
use App\User;
use Illuminate\Support\Facades\Log;

class SetUserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     *
     * @todo Once permission is set on user level this code will be deleted
     * @uses  this is just one time seeder to set user level permission
     */

    public function run()
    {

        $allUserData= User::get();

        $permissionData= Permission::pluck('id');
        foreach ($allUserData as $k =>$v)
        {
            //this is for super-admin
            if($v->role_id==config('constants.user.role.super_admin'))
            {
                $userPermissionData=[];
                foreach ($permissionData as $value)
                {
                    $userPermissionData[$value]=true;
                    if($value==config('constants.PERMISSION.EMAIL'))
                    {
                        $userPermissionData[$value]=false;
                    }
                }
            }

            //for all other role
            else
            {
                //getting all role base permission id
                $tempOrganizationRolePermissionData= OrganizationRolePermission::where('organization_id',$v->organization_id)
                    ->where('role_id',$v->role_id)
                    ->pluck('permission_id');

                //getting all organization  permission
                $permissionData= OrganizationRolePermission::where('organization_id',$v->organization_id)
                    ->where('role_id',config('constants.user.role.admin'))
                    ->pluck('permission_id');


                $userPermissionData=[];
                foreach ($permissionData as $value)
                {
                    if($tempOrganizationRolePermissionData->search($value,true))
                    {
                        $userPermissionData[$value]=true;
                    }
                    else
                    {
                        $userPermissionData[$value]=false;
                    }
                }
            }
            $v->user_permission=json_encode($userPermissionData);
            $v->save();
        }
        Log::info("\n\n========================User Permission Reset ===========================\n\n");


    }
}
