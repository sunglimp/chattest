<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class InformVisitorChannel implements ShouldBroadcastNow
{
    
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


    public function broadcastOn()
    {
        return new Channel($this->eventInfo['channel_name']);
    }
    
    public function broadcastWith()
    {
        return $this->eventInfo;
    }
}
