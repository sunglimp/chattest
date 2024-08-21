<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ChatChannel;
use App\Models\ChatMessage;
use App\Models\UserOnlineStatus;
use App\Observers\ChatChannelObserver;
use App\Observers\ChatMessageObserver;
use App\Observers\UserOnlineStatusObserver;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        ChatChannel::observe(ChatChannelObserver::class);
        ChatMessage::observe(ChatMessageObserver::class);
        UserOnlineStatus::observe(UserOnlineStatusObserver::class);
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
