<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class ChannelCollection extends ResourceCollection
{
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
        $clientDisplayFlag    = false;
        $clientDisplaySetting = null;

        if($loggedInUser->checkPermissionBySlug('identifier_masking')) {
            $identifierPermission = true;
        }

        if($loggedInUser->checkPermissionBySlug('customer_information')) {
            $clientDisplayFlag = true;
            $setting = $loggedInUser->getPermissionSetting('customer_information');
            $clientDisplaySetting = isset($setting['whatsapp']) ? $setting['whatsapp']['client_display_attribute'] : config('constants.CUSTOMER_CHAT_LABEL.WHATSAPP.NUMBER');
        }

        return [
            'data'=> $this->collection->transform(function (Channel $channel) use ($identifierPermission, $clientDisplayFlag, $clientDisplaySetting) {;
                return (new Channel($channel, $identifierPermission, $clientDisplayFlag, $clientDisplaySetting));
            })
        ];

        //dd($clientDisplaySetting);

        //return parent::toArray($request);
    }
}
