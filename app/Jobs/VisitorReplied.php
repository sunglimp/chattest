<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\InformAgentPrivateChannel;


class VisitorReplied implements ShouldQueue
{

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    private $chatChannel;
    private $responseDelay;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($chatChannel, $delay = 60)
    {
        $this->chatChannel = $chatChannel;
        $this->responseDelay = $delay;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        info('Job Visitor Replied executed::', ['channel' => $this->chatChannel->id, 'delay' => $this->responseDelay]);
        $difference = $this->chatChannel->chatChannelResponseTiming->agent_responded_at - $this->chatChannel->chatChannelResponseTiming->visitor_responded_at;
        
        $actualAgentDelay = now()->timestamp - $this->chatChannel->chatChannelResponseTiming->agent_responded_at;

        if ($difference < 0 && $actualAgentDelay >= $this->responseDelay) {
            broadcast(
                    new InformAgentPrivateChannel([
                        'event'    => config('broadcasting.events.new_important_notifier'),
                        'id'       => $this->chatChannel->id,
                        'agent_id' => $this->chatChannel->agent_id,
                ]))->toOthers();
            info('Job Visitor Replied executed::', ['reply_difference' => $difference, 'actual_delay' => $actualAgentDelay]);
        }
        else
        {
            info('Job Visitor Replied not executed::', ['reply_difference' => $difference, 'actual_delay' => $actualAgentDelay]);
        }
    }

}
