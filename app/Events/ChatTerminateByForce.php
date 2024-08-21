<?php

namespace App\Events;

class ChatTerminateByForce
{
    public $userId;
    public $channelId;
    public $allLogout;
   

    public function __construct($userId, $channelId,$allLogout=0)
    {
        $this->userId = $userId;
        $this->channelId = $channelId;
        $this->allLogout = $allLogout;
    }
}
