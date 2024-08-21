<?php

use Illuminate\Database\Seeder;

class ChatChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\ChatChannel::class, 500)->create();
    }
}
