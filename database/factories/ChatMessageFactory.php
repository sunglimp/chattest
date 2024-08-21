<?php

use Faker\Generator as Faker;

$factory->define(App\Models\ChatMessage::class, function (Faker $faker) {
    $recipient = ['AGENT', 'VISITOR'][rand(0,10)];
    if($recipient == 'AGENT')
    {
        cache(['factory_thread' => str_random(50)]);
    }
    return [
        'chat_channel_id' => rand(350, 375),//\App\Models\ChatChannel::inRandomOrder()->first()->id,
        'message' => json_encode(['text' => $faker->text(rand(50,100))]),
        'recipient' => $recipient,
    ];
});
