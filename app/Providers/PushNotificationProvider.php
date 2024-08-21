<?php

namespace App\Providers;

use App\Libraries\Contracts\PushNotificationInterface;
use App\Libraries\FCMNotification;
use Illuminate\Support\ServiceProvider;

class PushNotificationProvider extends ServiceProvider
{
     /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        return $this->app->singleton(PushNotificationInterface::class, function() {
            return new FCMNotification();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [PushNotificationInterface::class];
    }
}
