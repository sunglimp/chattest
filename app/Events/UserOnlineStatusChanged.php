<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class UserOnlineStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $agentId;
    public $status;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($agentId, $status)
    {
        $this->agentId = $agentId;
        $this->status = $status;
    }
}
