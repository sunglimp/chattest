<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Libraries\SurboAPI;
use App\Libraries\TMSAPI;

class TMSAPIProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        return $this->app->bind('TMSAPI', function () {
            return new TMSAPI();
        });
    }
}
