<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ChatMessagesHistory;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class Channel extends JsonResource
{
    private $identifierPermission;
    private $clientDisplaySetting;
    private $clientDisplayFlag;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $identifierPermission=false, $clientDisplayFlag=false, $clientDisplaySetting  = null)
    {
        parent::__construct($resource);
        $this->identifierPermission  = $identifierPermission;
        $this->clientDisplayFlag     = $clientDisplayFlag;
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
        $client     = json_decode($this->raw_info);

        if($this->identifierPermission) {
            $client->{$this->source_type}->identifier =  isset($client->{$this->source_type}->identifier) ? mask($client->{$this->source_type}->identifier): null;
            $client->identifier =  isset($client->identifier) ? mask($client->identifier) : null;
        }

        if($this->source_type=='whatsapp' && $this->clientDisplayFlag) {
            $clientName = isset($client) && isset($client->{$this->source_type}) && isset($client->{$this->source_type}->name) ? $client->{$this->source_type}->name : null;
            $this->client_display_name = client_display_name($this->clientDisplaySetting, $this->identifierPermission, $this->client_display_name, $clientName);
            if ($this->identifierPermission && $this->clientDisplaySetting!= config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER')) {
                $client->{$this->source_type}->name =  isset($client->{$this->source_type}->name) ? mask($client->{$this->source_type}->name): null;
                $client->name =  isset($client->name) ? mask($client->name) : null;
            }
        } else {
            $this->client_display_name = $this->identifierPermission ? mask($this->client_display_name): $this->client_display_name;
        }

        $recentMsgHistory=  ChatMessagesHistory::unreadCountByRootChannel($this->root_channel_id ?? $this->id);

        $recentMsgCurrent=  ChatMessage::recentMessagePlusUnreadCount($this->id);
        return [
            'id'             =>  $this->id,
            'channel_name'   =>  $this->channel_name,
            'group_id'       => $this->group_id,
            'client_id'      => $this->client_id,
            'parent_id'      =>  $this->parent_id,
            'agent_name'     =>   $this->name,
            'role'           =>   $this->role_name,
            'client_display_name' => $this->client_display_name,
            'source_type'         => $this->source_type,
            'client_raw_info'     => $client,
            'unread_count'        => $recentMsgHistory['unread_count'] + $recentMsgCurrent['unread_count'],//Avoiding heavy joins. This may be driven using some cache logic later
            'recent_message'      => $recentMsgCurrent['recent_message'] ?? $recentMsgHistory['recent_message'],
            'channel_agent_id'    => $this->agent_id,
            'channel_type'        => $this->channel_type,
            'status'              => $this->status,
            'has_history'         => $this->history_status
        ];
    }
}
