<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Resources\Json\Resource;
use App\Providers\TelescopeServiceProvider;

class   AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \DB::listen(function ($query) {
            info('Query :: ' . $query->sql);
            info('Bindings :: ' . json_encode($query->bindings));
            info('Execution Time :: ' . $query->time);
        });
        
        if(in_array(config('app.env'), ['production', 'uat', 'qa']))
        {
            URL::forceScheme('https');
        }
        
        Resource::make(['status' => 'success']);


    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(TelescopeServiceProvider::class);
    }
}
