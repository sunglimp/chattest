<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

use App\Models\OrganizationRolePermission;
use App\Observers\OrganizationRolePermissionObserver;

class OrganizationRolePermissionProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
//        Log::info("===================OrganizationRolePermissionObserver Provider====  ");
        OrganizationRolePermission::observe(OrganizationRolePermissionObserver::class);

    }
}
