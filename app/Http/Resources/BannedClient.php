<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class BannedClient extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
            $loggedInUser = Auth::user();
            return [
                'id' => $this->client_id,
                'channel_id' => $this->chat_channel_id,
                'client_display_name' => isset($this->identifier)?$this->identifier:'Guest',
                'source_type' => $this->source_type??'web',
                'client_raw_info' => json_decode($this->raw_info),
                'date' => \Carbon\Carbon::createFromTimestamp($this->banned_at, $loggedInUser->timezone)->format('M d, Y')
            ];
    }
}
