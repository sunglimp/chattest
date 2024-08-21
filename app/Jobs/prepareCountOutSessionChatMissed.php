<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ChatChannel;

class prepareCountOutSessionChatMissed 
{
    use Dispatchable, SerializesModels;

    protected $now;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($now)
    {
        $this->now = $now;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ChatChannel::getOutSessionMissedChats($this->now);
    }
}
