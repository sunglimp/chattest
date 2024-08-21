<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class MissedChatClient extends JsonResource
{

    private $identifierPermission;
    private $message;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $identifierPermission=false, $msg=[])
    {
        parent::__construct($resource);
        $this->identifierPermission = $identifierPermission;
        $this->message = $msg;
    }

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

            'client_display_name' => isset($this->identifier) ? ($this->identifierPermission ? mask($this->identifier) : $this->identifier) : 'Guest',
            'chat_channel_id' => $this->chat_channel_id,
            'client_id' => $this->client_id,
            'source_type'=> $this->source_type,
            'status' => empty($this->status) ? 0 : $this->status,
            'message' => $this->message[$this->status] ?? $this->message[0],
            'date' => \Carbon\Carbon::createFromTimestamp($this->createdDate, $loggedInUser->timezone)->format('M d, Y H:i'),

        ];
    }
}
