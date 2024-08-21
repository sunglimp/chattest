<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\OrganizationRolePermission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\Permission;
use Response;
use Auth;

class PermissionController extends Controller
{
    //

    public function getAllPermissionByUser(Request $request)
    {

        $user = User::find(Auth::user()->id);

        $data= OrganizationRolePermission::where('organization_id',$user->organization_id)
                                    ->where('role_id',$user->role_id)
                                    ->get();
        $permission= Permission::select('id','name','slug')->get();
        foreach ($permission as $k =>$v)
        {
            if(in_array($v->id, array_column($data->toArray(), 'permission_id'))) { // search value in the array
                $permission[$k]->status=True;
//                unset($permission[$k]->id);
                    }
            else
            {
                $permission[$k]->status=False;
            }
        }
        return  Response::json($permission);
    }
}
