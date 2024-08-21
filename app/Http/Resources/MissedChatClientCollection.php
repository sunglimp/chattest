<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class MissedChatClientCollection extends ResourceCollection
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

        if($loggedInUser->checkPermissionBySlug('identifier_masking')) {
            $identifierPermission = true;
        }
        $msg  = [
            config('constants.MISSED_CHAT_ACTION.CONTACT') => default_trans($loggedInUser->organization->id . '/missed_chat.status.contact', __('default/missed_chat.status.contact')),
            config('constants.MISSED_CHAT_ACTION.WA_PUSHED') => default_trans($loggedInUser->organization->id . '/missed_chat.status.contacted', __('default/missed_chat.status.contacted')),
            config('constants.MISSED_CHAT_ACTION.REJECTED') => default_trans($loggedInUser->organization->id . '/missed_chat.status.rejected', __('default/missed_chat.status.rejected'))
         ];
        return [
            'data'=> $this->collection->transform(function (MissedChatClient $client) use ($identifierPermission, $msg) {
                return (new MissedChatClient($client,$identifierPermission, $msg));
            })
        ];
    }
}
