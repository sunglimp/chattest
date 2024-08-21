<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ClientList extends JsonResource
{

    protected $allTagged;
    protected $taggedChats;
    private $identifierPermission;
    private $clientDisplaySetting;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $identifierPermission=false, $allTagged=false, $clientDisplaySetting  = null)
    {
        parent::__construct($resource);
        $this->identifierPermission = $identifierPermission;
        $this->allTagged = $allTagged;
        $this->clientDisplaySetting  = $clientDisplaySetting;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Manipulating Client Display Name based on Customer Information Setting Permission
        if ($this->source_type=='whatsapp' && $this->clientDisplaySetting && isset($this->identifier)) {
            $client     = json_decode($this->raw_info, true);
            $clientName = isset($client) && isset($client[$this->source_type]) && isset($client[$this->source_type]['name']) ? $client[$this->source_type]['name'] : $this->identifier;
            $client_display_name = client_display_name($this->clientDisplaySetting, $this->identifierPermission, $this->identifier, $clientName);
        } else {
            $client_display_name = isset($this->identifier) ? ($this->identifierPermission ? mask($this->identifier) : $this->identifier) : 'Guest';
        }
        // Manipulating Client Display Name based on Customer Information Setting Permission
        $loggedInUser = Auth::user();
        return [
            'id' => $this->client_id,
            'is_tagged' => $this->allTagged ? 1 : ((isset($this->tagged) && $this->tagged) ? 1 : 0) ,
            'channel_id' => $this->chat_channel_id,
            'client_display_name' => $client_display_name,
            'source_type'=> $this->source_type,
            'date' => \Carbon\Carbon::createFromTimestamp($this->createdDate, $loggedInUser->timezone)->format('M d, Y')
        ];
    }
}
