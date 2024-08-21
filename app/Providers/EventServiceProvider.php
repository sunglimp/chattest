<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\UserOnlineStatusChanged;
use App\Events\MessageArrived;
use App\Listeners\UserAuthEventListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\UserOnline' => [
            'App\Listeners\MakeOnlineInCache',
            'App\Listeners\AssignWaitingChannelsListener'
        ],
        'App\Events\UserOffline' => [
            'App\Listeners\MakeOfflineInCache',
        ],
        'App\Events\ChatTerminate' => [
            'App\Listeners\MakeSlotFree',
            'App\Listeners\CloseWhatsAppChat'
        ],
        'App\Events\ChatTransfer' => [
            'App\Listeners\MakeSlotFree',
        ],
        'App\Events\ChatTerminateWhatsapp' => [
            'App\Listeners\CloseWhatsAppChat'
        ],



        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\LoginListener::class
        ],
        \Illuminate\Auth\Events\Logout::class => [
            \App\Listeners\LogoutListener::class
        ]
    ];
    
    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        'App\Listeners\UserAuthEventListener',
        'App\Listeners\MakeChatArchivedListener',

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
