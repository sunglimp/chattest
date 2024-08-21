<?php

namespace App\Console\Commands;

use App\Models\ChatChannel;
use Illuminate\Console\Command;

class CleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All the tasks related to clean up the databases';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->deleteChatChannelResponseTimingEntries();
    }
    
    private function deleteChatChannelResponseTimingEntries()
    {
        \App\Models\ChatChannelResponseTiming::whereIn(
                'chat_channel_id',
                ChatChannel::closedChannels()->get()->pluck('id'))
        ->delete();
    }
}
