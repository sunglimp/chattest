<?php

use Faker\Generator as Faker;

/*
  |--------------------------------------------------------------------------
  | Model Factories
  |--------------------------------------------------------------------------
  |
  | This directory should contain each of the model factory definitions for
  | your application. Factories provide a convenient way to generate new
  | model instances for testing / seeding your application's database.
  |
 */

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name'            => $faker->name,
        'email'           => $faker->unique()->safeEmail,
        'password'        => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'mobile_number'   => $faker->numberBetween(6500000000, 9999999999),
        'organization_id' => \App\Models\Organization::inRandomOrder()->first()->id,
        'report_to'       => \App\User::inRandomOrder()->first()->id,
        'role_id'         => \App\Models\Role::inRandomOrder()->first()->id,
        'gender'          => $faker->randomElement(['male', 'female']),
        'no_of_chats'     => rand(5, 10),
        'timezone'        => $faker->timezone,
        'remember_token'  => str_random(10),
        'created_at'      => \Carbon\Carbon::now(),
    ];
});
