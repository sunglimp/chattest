<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\User;
use Carbon\Carbon;

class BannnedClientChat extends JsonResource
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
            'source_type'  => $this->source_type,
            'created_at'   =>  Carbon::createFromTimestamp($this->chat_created_at)
            ->timezone($this->timezone),
            'agent_display_name' => $this->message_type == 'BOT' ? 'Surbo' : $this->agent_display_name
        ];
    }
}
