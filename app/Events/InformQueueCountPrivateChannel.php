<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
//use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\User;
use Illuminate\Support\Facades\Log;

class InformQueueCountPrivateChannel implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $agentId;
    public $eventInfo;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($eventInfo=[])
    {
        $this->agentId = $eventInfo['agent_id'] ?? '';
        $this->eventInfo = $eventInfo;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
       return new Channel('chat-queue-count-' . $this->agentId);
    }
    
    public function broadcastWith()
    {
        info($this->eventInfo);
        return $this->eventInfo;
    }
}
