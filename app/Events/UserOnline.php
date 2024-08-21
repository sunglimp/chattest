<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class UserOnline
{

    public $userId;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }
}
