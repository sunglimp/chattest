<?php

namespace App\Observers;

use App\Models\OrganizationRolePermission;
use Illuminate\Support\Facades\Log;

class OrganizationRolePermissionObserver
{
    //




    public function created(OrganizationRolePermission $data )
    {
        //
        dd($data);



    }

    /**
     * Handle the user "updated" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function updated(OrganizationRolePermission $data)
    {
        //

        dd($data);

    }

    public function saving(OrganizationRolePermission $data)
    {
        dd($data);

    }

    public function deleting(OrganizationRolePermission $data)
    {
        Log::info("===================OrganizationRolePermissionObserver Provider====  ");



    }

    public function deleted(OrganizationRolePermission $data)
    {
        Log::info("===================OrganizationRolePermissionObserver Provider====  ");


    }
}
