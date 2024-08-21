<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\Models\ChatMessage;

class MessageArrived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    

    public $chatMessage;
    public $channelName;
    public $senderDisplayName;
    public $attachmentPath;
    

    public function __construct(ChatMessage $chatMessage, $channelName, $senderDisplayName)
    {
        $this->chatMessage = $chatMessage;
        $this->channelName = $channelName;
        $this->senderDisplayName = $senderDisplayName;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {

        return new Channel($this->channelName);
    }
    
    public function broadcastWith()
    {
        $message = json_decode($this->chatMessage->message, true);
        return  [
            'message' => $message,
            'recipient' => $this->chatMessage->recipient,
            'message_type' => $this->chatMessage->message_type,
            'sender_display_name' => $this->senderDisplayName,
            'attachment_path' =>  !empty($message['path']) ? url($message['path']) : null,
            'source_type' => $this->chatMessage->source_type
        ];


    }
}
