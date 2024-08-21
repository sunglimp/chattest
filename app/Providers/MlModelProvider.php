<?php

namespace App\Providers;

use App\Libraries\MlModelAPI;
use App\Libraries\TMSAPI;
use Illuminate\Support\ServiceProvider;

class MlModelProvider extends ServiceProvider
{


    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        return $this->app->bind('MlModelAPI', function () {
            return new MlModelAPI();
        });
    }
}
