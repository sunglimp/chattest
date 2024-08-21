<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Policies\PermissionPolicy;
use App\Models\Permission;
use App\Models\CannedResponse;
use App\Policies\CannedResponsePolicy;
use Illuminate\Support\Facades\Auth;

//use App\Extensions\AccessTokenGuard;
//use App\Extensions\TokenToOrganizationProvider;
//use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */

    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        'App\Models\Group'=> 'App\Policies\GroupPolicy',
         Permission::class => PermissionPolicy::class,
        CannedResponse::class => CannedResponsePolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        //dd(Auth::user());
        if (Auth::check() && Auth::user()->id !=1) {
            if (Auth::user()->language) {
                $this->app->instance('path.lang', $this->app['path.lang']. DIRECTORY_SEPARATOR. Auth::user()->organization_id);
                $this->app->setLocale(Auth::user()->language);
            }
        }
        // dd($this->app['path.lang']);
        Gate::define('all-admin', function ($user) {
            return  $user->role_id==1 || $user->role_id==2;
        });
        
        Gate::define('not-admins', function ($user) {
            return !in_array($user->role_id, [1, 2]);
        });
        
        Gate::define('superadmin', function ($user) {
            return  $user->role_id==1;
        });

        Gate::define('admin', function ($user) {
            return  ($user->role_id==2);
        });

        Gate::define('manager', function ($user) {
            return  ($user->role_id==3);
        });

        Gate::define('teamlead', function ($user) {
            return  ($user->role_id==4);
        });

        Gate::define('associate', function ($user) {
            return  ($user->role_id==5);
        });
        
        Gate::define('not-superadmin', function ($user) {
            return  !($user->role_id==1);
        });
        
        Gate::resource('groups', 'App\Policies\GroupPolicy');
    }
}
