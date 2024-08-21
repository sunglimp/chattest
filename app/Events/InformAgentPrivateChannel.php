<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
//use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\User;

class InformAgentPrivateChannel implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $eventInfo = [];

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($eventData = [])
    {

        $this->eventInfo = $eventData;
        info($this->eventInfo);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
       
        //@TODO This channel will be type of private, once authentication get finalized
        return new Channel('hash-agent-' . $this->eventInfo['agent_id']);
        //return new PrivateChannel('agent-' . $this->eventInfo['agent_id']);
    }
    
    public function broadcastWith()
    {
        return $this->eventInfo;
    }
}
