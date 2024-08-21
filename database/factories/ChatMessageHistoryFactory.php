<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\ChatMessagesHistory::class, function (Faker $faker) {
    $user = \App\User::whereNotIn('role_id', [1,2])->inRandomOrder()->first();
    return [
        'organization_id' => $user->organization_id,
        'group_id' => \App\Models\Group::where('organization_id', $user->organization_id)->first()->id,
        'chat_channel_id' => \App\Models\ChatChannel::inRandomOrder()->first()->id,
        'client_id' => \App\Models\Client::inRandomOrder()->first()->id,
        'user_id'=> $user->id,
        'message' => json_encode(['text' => $faker->text(rand(50,200))]),
        'read_at' => \Carbon\Carbon::now()->subDay(rand(1,30))->subMinutes(rand(5,15))->timestamp,
        'recipient' => ['visitor', 'agent','visitor', 'agent','visitor', 'agent','visitor', 'agent','visitor', 'agent'][rand(0,9)],
        'message_type' => 'public'
        
    ];
});
