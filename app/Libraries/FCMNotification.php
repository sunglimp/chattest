<?php

namespace  App\Libraries;

use App\Libraries\Contracts\PushNotificationInterface;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Facades\FCM;



class FCMNotification implements PushNotificationInterface {

    public function sendTo($to, array $data = [])
    {
        try {

            $title = $data['title'] ?? 'Default Title';
            $body  = $data['body'] ?? 'Default Message';

            $optionBuilder = new OptionsBuilder();
            $optionBuilder->setPriority('high');

            $notificationBuilder = new PayloadNotificationBuilder($title);
            $notificationBuilder->setBody($body)
                                ->setSound('default');

            /*********************Optional Parameters check***************/
            if (isset($data['color']) && !empty($data['color'])) {
                $notificationBuilder->setColor($data['color']);
            }
            /*********************Optional Parameters check***************/

            // Condition to check the data in the notification
            $notification_data = null;
            if(isset($data['data']) && !empty($data['data']))
            {
                $dataBuilder = new PayloadDataBuilder();
                $dataBuilder->addData($data['data']);
                $notification_data = $dataBuilder->build();
            }

            $option = $optionBuilder->build();
            $notification = $notificationBuilder->build();
            $downstreamResponse = FCM::sendTo($to, $option, $notification, $notification_data);
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
        return false;
    }
}