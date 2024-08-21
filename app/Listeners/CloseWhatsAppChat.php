<?php

namespace App\Listeners;

use App\Models\ChatChannel;
use GuzzleHttp\Client;
use App\Facades\SurboAPIFacade;
use App\Libraries\SurboAPI;
use App\Models\Group;
use App\Models\PermissionSetting;
use App\Models\OrganizationRolePermission;
use App\User;

class CloseWhatsAppChat
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $channelId = $event->channelId;
        $channel = ChatChannel::find($channelId);
        $user = User::find($channel->agent_id);
        if (!empty($channel->token) && !empty($channel->end_point)) {
            if(isset($event->allLogout) && $event->allLogout == 1){
            $organizationId = Group::getOrganizationIdByGroup($channel['group_id']);
            $messageData = PermissionSetting::where('organization_id', $organizationId)->where('permission_id', config('constants.PERMISSION.OFFLINE-FORM'))->select('settings->message as message')->first();
             $offlineData['show_offline_form'] = OrganizationRolePermission::where('organization_id', $organizationId)
                    ->where('role_id', config('constants.user.role.admin'))
                    ->where('permission_id', config('constants.PERMISSION.OFFLINE-FORM'))
                    ->exists();

            /**
             * @todo vatika is hardcoded need to be dynamic or removed
             */
            $message    = (($offlineData['show_offline_form'])
                            && ($messageData->message != 'null'))
                                ? json_decode($messageData->message,'JSON_NUMERIC_CHECK')
                                : __('message.no_agent_online', ['organization' => 'Vatika']);    
            $body = [
                'event' => 'chat_close_by_agent',
                'channel_name' => $channel->channel_name,
                'show_feedback_form' => false,
                'message'=> $message
            ];
            }
            else{
              $body = [
                'event' => 'chat_close_by_agent',
                'channel_name' => $channel->channel_name,
                'show_feedback_form' => !is_null($user) ? $user->checkPermissionBySlug('chat-feedback') : false
            ];   
            }
            
           
            $headers = [
                'Authorization' => 'Bearer ' . $channel->token,
            ];
            
           info($headers);
           info($body);
            try {
                $url = rtrim($channel->end_point, '/').config('whatsapp.api_url_close');

                SurboAPIFacade::request(SurboAPI::POST, $url, $body, $headers)->getResponse();
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
      }
        }
    }