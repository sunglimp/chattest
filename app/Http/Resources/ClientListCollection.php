<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class ClientListCollection extends ResourceCollection
{

    protected $allTagged;

    public function __construct($clientInfo)
    {
        parent::__construct($clientInfo[0]);
        $this->allTagged = $clientInfo[1];
    }
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $loggedInUser = Auth::user();
        $identifierPermission = false;
        $clientDisplaySetting = null;

        if($loggedInUser->checkPermissionBySlug('identifier_masking')) {
            $identifierPermission = true;
        }

        if($loggedInUser->checkPermissionBySlug('customer_information')) {
            $setting = $loggedInUser->getPermissionSetting('customer_information');
            $clientDisplaySetting = isset($setting['whatsapp']) ? $setting['whatsapp']['client_display_attribute'] : config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER');
        }

        return [
            'data'=> $this->collection->transform(function (ClientList $client) use ($identifierPermission, $clientDisplaySetting ) {;
                return (new ClientList($client, $identifierPermission, $this->allTagged, $clientDisplaySetting ));
            })
        ];

        /*$this->collection->transform(function (ClientList $client) use ($identifierPermission) {
            return (new ClientList($client,$identifierPermission));
        });

        return parent::toArray($request);*/
    }
}
