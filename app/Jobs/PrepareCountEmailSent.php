<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use App\Models\EmailContent;

class PrepareCountEmailSent
{
    use Dispatchable, SerializesModels;
    
    protected $now;
    protected $agents;


    /**
     * Create a new job instance.
     *
     * @return void
     */
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
        try {
            EmailContent::getCountEmailSent($this->now, $this->agents);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
}
