<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Libraries\SurboAPI;

class SurboAPIProvider extends ServiceProvider
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
        return $this->app->bind('SurboAPI', function() {
            return new SurboAPI();
        });
    }
}
