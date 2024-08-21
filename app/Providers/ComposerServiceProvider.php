<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //

        View::composer(
            ['layouts.side-bar'],
            'App\Http\ViewComposers\SidebarComposer'
        );
        
        View::composer('*', 'App\Http\ViewComposers\ApplicationComposer');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
