<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('agent-{id}', function ($user, $id) {
     return true;//return (int) $user->id === (int) $id;
});



Broadcast::channel('test-event', function ($user) {
    return true;
});

Broadcast::channel('subscribers-{agentId}', App\Broadcasting\Subscribers::class);
Broadcast::channel('supervision-{channel}', App\Broadcasting\Supervision::class);
