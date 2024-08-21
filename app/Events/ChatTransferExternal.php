<?php

namespace App\Events;

class ChatTransferExternal
{

    public $channelId;
    public $groupId; //
    public $chatTransferData;

    public function __construct($channelId, $groupId, $chatTransferData)
    {
        $this->channelId = $channelId;
        $this->groupId   = $groupId;
        $this->chatTransferData = $chatTransferData;
    }
}
