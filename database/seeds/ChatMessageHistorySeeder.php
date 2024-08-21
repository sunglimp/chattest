<?php

use Illuminate\Database\Seeder;

class ChatMessageHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\ChatMessagesHistory::class, 1)->create();
    }
}
