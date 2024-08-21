<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\ExpireChatRepository;
use Illuminate\Support\Facades\Log;


class ExpireChatClose implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     public $channelId;
     
        
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($channelId)
    {
        $this->channelId = $channelId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {      
        try { 
            Log::debug("ExpireChatClose:Initiated chat close for channel id ". $this->channelId);
            (new ExpireChatRepository)->closeChat($this->channelId);
       } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
