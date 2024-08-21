<?php

namespace App\Observers;

use App\Models\ChatMessage;
use App\Models\ChatChannel;
use App\Models\ChatChannelResponseTiming;
use Illuminate\Queue\Jobs\Job;
use  App\Jobs\ChatResponseTimingUpdate;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Cache;
use Carbon\Carbon;

class ChatMessageObserver
{
   

    public function creating(ChatMessage $chatMessage)
    {

        $channelName = request()->channel_name;
        if ($chatMessage->recipient == ChatMessage::RECIPIENT_AGENT && !$chatMessage->isInternalChat()) {
            $activeChannel = ChatChannel::getActiveChannel($channelName);
            $chatMessage->chat_channel_id = $activeChannel->id;
            $this->setReplyWaitingTime($channelName);
            if (!empty($activeChannel->agent_id)) {
                send_chat_notification($activeChannel->agent_id, config('constants.CHAT_NOTIFICATION_EVENTS.NEW_MESSAGE'));
            }
        } else if ($chatMessage->recipient == ChatMessage::RECIPIENT_VISITOR && $chatMessage->isPublicMessage()) {
            $agentReplyAfterSec = $this->getAgentResponseTimeGap($channelName);
            if ($agentReplyAfterSec > 0) {
                $chatMessage->response_within = $agentReplyAfterSec;
            }      

        }
    }


    /**
     * Handle the chat message "created" event.
     *
     * @param  \App\ChatMessage  $chatMessage
     * @return void
     */
    public function created(ChatMessage $chatMessage)
    {
        
        
        info('message arrived::' . json_encode($chatMessage));
        if ($chatMessage->isPublicMessage()) {
            ChatResponseTimingUpdate::dispatch($chatMessage->toArray())->onQueue(config('chat.queues.response_time_update'));
            
        }

        if ($chatMessage->isInternalChat()) {
            $chatMessage->createWithInternalChat();
        } else {
            broadcast(new \App\Events\MessageArrived($chatMessage, request()->get('channel_name'), request()->get('sender_display_name')))->toOthers();
            if($chatMessage->isAgentMessage()) {
                $chatChannel = $chatMessage->chatChannel;

                if (!empty($chatChannel->end_point) && !empty($chatChannel->token)) {
                    $message = json_decode($chatMessage->message, true);

                    $body = [
                        'message' => $message,
                        'recipient' => $chatMessage->recipient,
                        'message_type' => $chatMessage->message_type,
                        'sender_display_name' => request()->get('sender_display_name'),
                        'attachment_path' => !empty($message['path']) ? url($message['path']) : null
                    ];

                    $client = new Client();

                    $headers = [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $chatChannel->token,
                    ];

                    try {
                        $url = rtrim($chatChannel->end_point, '/').config('whatsapp.api_url_send');
                        info('request initiated to ' .$url . ' with', $body);
                       
                        $request = $client->post($url, [
                            'headers' => $headers,
                            'json' => $body
                        ]);
                        if(!empty($request->getBody()))
                        {
                            info('response body', json_decode($request->getBody()->getContents(), true));
                        }

                    } catch (\Exception $e) {
                        \Log::error($e->getMessage());
                    }

                }
            }
        }
    }

    private function setReplyWaitingTime($channelName){
        //reply_waiting_from_{timestamp} i.e. short : rwf
        $cacheKey = 'rwf_'. $channelName;
        if (Cache::has($cacheKey)) {
            $visitorMessageTime = Cache::get($cacheKey);
            if ($visitorMessageTime[1]) {
                //If previous message has been answered then store new message time
                Cache::forever($cacheKey, [Carbon::now()->timestamp, false]);
            }
        } else {
            //First time visitor ask 
            Cache::forever($cacheKey, [Carbon::now()->timestamp, false]);
        }
    }
    private function getAgentResponseTimeGap($channelName){
        //reply_waiting_from_{timestamp} i.e. short : rwf
        $cacheKey = 'rwf_'. $channelName;
        if (Cache::has($cacheKey)) {
            $visitorMessageTime = Cache::get($cacheKey);
            if ($visitorMessageTime[1] === false) {
                $visitorMessageTime[1] = true;
                Cache::forever($cacheKey, $visitorMessageTime);
                return Carbon::now()->timestamp - $visitorMessageTime[0];
            }
        }
        return false;
    }

}
