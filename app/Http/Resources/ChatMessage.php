<?php

namespace App\Http\Resources;

use App\Agent;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ChatMessage as Message;

class ChatMessage extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array


     */
    public function toArray($request)
    {
        return [

            'message' => json_decode($this->message, true),
            'recipient' => $this->recipient,
            'message_type' => $this->message_type,
            'created_at' => $this->created_at,
            'read_at' => $this->read_at,
            'source_type'=> $this->source_type,
            'agent_display_name' => $this->message_type == Message::MESSAGE_TYPE_BOT
                ? 'Surbo'
                : ($this->internal_agent_id ? Agent::displayName($this->internal_agent_id) : $this->agent_display_name)
        ];
    }

    
}