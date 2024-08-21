<?php

use Faker\Generator as Faker;
use App\Models\ChatChannel;
$factory->define(ChatChannel::class, function (Faker $faker) {
    $agent = \App\User::whereNotIn('role_id', [1,2])->inRandomOrder()->first();
    $return = [
            'channel_name' => 'visitor_'. str_random(10),
            'agent_id' => $agent->id,
            'group_id' => \App\Models\Group::where('organization_id', $agent->organization_id)->first()->id,
            'client_id' => \App\Models\Client::inRandomOrder()->first()->id,
            'status' => rand(1,5)
        ];
        switch ($return['status']) {
        case 1://unpicked
            $return['queued_at'] = \Carbon\Carbon::now()->subDay(rand(1,30))->timestamp;
            break;
        case 2://picked
            $subDay = rand(1,30);
            
            $return['queued_at'] = \Carbon\Carbon::now()->subDay($subDay)->timestamp;
            $return['accepted_at'] = \Carbon\Carbon::now()->subDay($subDay)->addMinute(rand(1,3))->timestamp;
            break;
        case 3://transferred
            $subDay = rand(1,30);
            $return['queued_at'] = \Carbon\Carbon::now()->subDay($subDay)->timestamp;
            $return['accepted_at'] = \Carbon\Carbon::now()->subDay($subDay)->addMinute(rand(1,3))->timestamp;
            $return['transferred_at'] = \Carbon\Carbon::now()->subDay($subDay)->addMinute(rand(4,6))->timestamp;
            break;
        case 4://terminated by agent
        case 5://terminated by visitor
            
            $subDay = rand(1,30);
            
            $return['queued_at'] = \Carbon\Carbon::now()->subDay($subDay)->timestamp;
            $return['accepted_at'] = \Carbon\Carbon::now()->subDay($subDay)->addMinute(rand(1,3))->timestamp;
            $return['terminated_at'] = \Carbon\Carbon::now()->subDay($subDay)->addMinute(rand(7,15))->timestamp;
            break;
    }
    
    return $return;
});
