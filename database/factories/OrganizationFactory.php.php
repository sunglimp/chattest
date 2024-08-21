<?php

use Faker\Generator as Faker;
use App\Models\Organization;

$factory->define(Organization::class, function (Faker $faker) {
    return [
        'company_name'     => $faker->company,
        'contact_name'     => $faker->unique()->name,
        'mobile_number'    => rand(6000000000, 9999999999),
        'email'            => $faker->email,
        'website'          => $faker->url,
        'logo'             => '',
        'seat_alloted'     => $faker->randomDigit,
        'surbo_unique_key' => str_random(20),
        'timezone'         => $faker->timezone,
        'created_at'       => time()
    ];
});
