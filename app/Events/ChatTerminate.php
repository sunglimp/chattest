<?php

namespace App\Events;

class ChatTerminate
{
    public $userId;
    public $channelId;
   

    public function __construct($userId, $channelId, $viaInternalTransfer, $isSessionTimeout=null)
    {
        $this->userId = $userId;
        $this->channelId = $channelId;
        $this->viaInternalTransfer = $viaInternalTransfer;
        $this->isSessionTimeout = $isSessionTimeout;
    }
}
