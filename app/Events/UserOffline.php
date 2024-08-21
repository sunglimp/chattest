<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class UserOffline
{

    public $userId;
    public $makeChatTerminated;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($userId, $makeChatTerminated=0)
    {
        $this->userId = $userId;
        $this->makeChatTerminated = $makeChatTerminated;
    }
}
