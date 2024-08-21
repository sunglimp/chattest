<?php

namespace App\Events;

class ChatTransferInternal
{

    public $channelId;
    public $agentId; //
    public $chatTransferData;

    public function __construct($channelId, $agentId, $chatTransferData)
    {
        $this->channelId = $channelId;
        $this->agentId = $agentId;
        $this->chatTransferData = $chatTransferData;
    }
}
