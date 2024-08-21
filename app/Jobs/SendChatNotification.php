<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PushNotification;
use Illuminate\Support\Facades\Log;

class SendChatNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $agentId;
    private $event;

    const LOGGED_IN = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($agentId, $event, array $options = [])
    {
        $this->agentId = $agentId;
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userInfo = $this->getUserDeviceToken();
        Log::debug("SendChatNotification::Event " . $this->event . " User " . $this->agentId);
        if(!empty($userInfo)) {
            $this->sendChatNotification($userInfo->organization_id, $userInfo->device_token);
            Log::debug("SendChatNotification::Success " . $this->event . " User " . $this->agentId);
        }

    }

    private function getUserDeviceToken()
    {
        // @TODO  we can have some caching/session technique here
        return User::select('device_token', 'organization_id')
                        ->join('login_histories', 'login_histories.user_id', '=', 'users.id')
                        ->where(['is_login'=>self::LOGGED_IN, 'chat_notification_status'=>config('constants.CHAT_NOTIFICATION_STATUS.ENABLE')])
                        ->where('logout_time', null)
                        ->where('device_type', '!=', config('constants.DEVICE_TYPE.Web'))
                        ->whereNotNull('device_token')
                        ->where('users.id', $this->agentId)
                        ->first();
    }

    private function sendChatNotification($organizationId, $token)
    {
        $data = array();
        try {
            switch ($this->event) {
                case config('constants.CHAT_NOTIFICATION_EVENTS.SESSION_TIMEOUT'):
                    $title = default_trans($organizationId.'/chat_notifications.session_timeout.title', __('default/chat_notifications.session_timeout.title'));
                    $body  = default_trans($organizationId.'/chat_notifications.session_timeout.body', __('default/chat_notifications.session_timeout.body'));
                    $data = ['title' => $title , 'body' => $body];
                    break;
                case config('constants.CHAT_NOTIFICATION_EVENTS.NEW_CHAT'):
                    $title = default_trans($organizationId.'/chat_notifications.new_chat_notification.title', __('default/chat_notifications.new_chat_notification.title'));
                    $body  = default_trans($organizationId.'/chat_notifications.new_chat_notification.body', __('default/chat_notifications.new_chat_notification.body'));
                    $data = ['title' => $title , 'body' => $body];
                    break;
                case config('constants.CHAT_NOTIFICATION_EVENTS.NEW_MESSAGE'):
                    $title = default_trans($organizationId.'/chat_notifications.new_message_notification.title', __('default/chat_notifications.new_message_notification.title'));
                    $body  = default_trans($organizationId.'/chat_notifications.new_message_notification.body', __('default/chat_notifications.new_message_notification.body'));
                    $data = ['title' => $title , 'body' => $body];
                    break;
                case config('constants.CHAT_NOTIFICATION_EVENTS.TRANSFER'):
                    $title = default_trans($organizationId.'/chat_notifications.transferred_chat_notification.title', __('default/chat_notifications.transferred_chat_notification.title'));
                    $body  = default_trans($organizationId.'/chat_notifications.transferred_chat_notification.body', __('default/chat_notifications.transferred_chat_notification.body'));
                    $data = ['title' => $title , 'body' => $body];
                    break;
                case config('constants.CHAT_NOTIFICATION_EVENTS.INTERNAL_COMMENT'):
                    $title = default_trans($organizationId.'/chat_notifications.internal_comment_notification.title', __('default/chat_notifications.internal_comment_notification.title'));
                    $body  = default_trans($organizationId.'/chat_notifications.internal_comment_notification.body', __('default/chat_notifications.internal_comment_notification.body'));
                    $data = ['title' => $title , 'body' => $body];
                    break;

                default:

                    break;
            }
            return PushNotification::sendTo($token, $data) ? true : false;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
