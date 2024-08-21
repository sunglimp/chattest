<?php

namespace App\Events;

class ChatTransfer
{
    public $userId;
    public $channelId;
    public function __construct($userId, $channelId)
    {
        $this->userId = $userId;
        $this->channelId = $channelId;
    }
}
