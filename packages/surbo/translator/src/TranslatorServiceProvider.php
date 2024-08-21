<?php

namespace Surbo\Translator;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use App\Models\Organization;

class TranslatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register the config publish path
        $configPath = __DIR__ . '/../config/translator.php';
        $this->mergeConfigFrom($configPath, 'translator');
        $this->publishes([$configPath => config_path('translator.php')], 'config');
        // Publish asset files
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/translator'),
        ], 'public');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        // Load packages migration
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // Registor the routes
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        
        // Add package command in artisan
        $this->commands([
            Console\Commands\ExportLocale::class,
            Console\Commands\ImportLocale::class,
         //   Console\Commands\AddLanguage::class
                ]);
        // Load view files path
        $viewPath = __DIR__.'/../resources/views';
        $this->loadViewsFrom($viewPath, 'translator');
        
        Organization::observe(OrganizationPackageObserver::class);
    }
}
