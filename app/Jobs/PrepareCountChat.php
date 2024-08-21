<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\ChatChannel;

class PrepareCountChat
{

    use Dispatchable,
        //InteractsWithQueue,
       // Queueable,
        SerializesModels;

    protected $now;
    protected $agents;
    
    public function __construct($now, $agents)
    {

        $this->now = $now;
        $this->agents = $agents;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        info('PrepareCountChat Handle:: ' . __METHOD__);
        
        ChatChannel::getChatCountOnDate($this->now, $this->agents);
    }
}
