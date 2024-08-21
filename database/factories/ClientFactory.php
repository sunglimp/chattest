<?php

use Faker\Generator as Faker;
use App\Models\Client;
$factory->define(Client::class, function (Faker $faker) {
    return [
        'email' => $faker->email,
        'mobile' => rand(6500000000, 9999999999),
        'raw_info'=> json_encode([
           'name'=> $faker->name,
            'email' => $faker->email,
            'mobile' => rand(6500000000, 9999999999),
            'ip' => $faker->ipv4
        ]),
    ];
});
