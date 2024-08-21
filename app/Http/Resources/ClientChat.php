<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientChat extends JsonResource
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
                'message' =>   json_decode($this->message),
                'recipient' => $this->recipient,
                'message_type' => $this->message_type,
                'created_at'   =>  $this->created_at,
                'source_type'  => $this->source_type,
                'agent_display_name' => $this->message_type == 'BOT' ? 'Surbo' : $this->agent_display_name
            ];
    }
}
