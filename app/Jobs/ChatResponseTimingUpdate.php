<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use  App\Models\ChatMessage;
use  App\Models\ChatChannel;
use  App\Models\ChatChannelResponseTiming;
use App\Repositories\ExpireChatRepository;

class ChatResponseTimingUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected  $chatMessage;

    public function __construct($aChatMessage = [])
    {

        $this->chatMessage = $aChatMessage;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        info($this->chatMessage);
        if($this->chatMessage['message_type'] == ChatMessage::MESSAGE_TYPE_PUBLIC)
        {
            if($this->chatMessage['recipient'] == ChatMessage::RECIPIENT_VISITOR)
            {

                $this->updateOrCreateResponseTime(
                    ['chat_channel_id' => $this->chatMessage['chat_channel_id']],
                    ['agent_responded_at' => $this->chatMessage['created_at']]);
                $isAgentRepondedFirstTime = ChatChannelResponseTiming::where('chat_channel_id', $this->chatMessage['chat_channel_id'])
                    ->whereNull('agent_first_responded_at')->first();
                if ($isAgentRepondedFirstTime) {
                    $isAgentRepondedFirstTime->agent_first_responded_at  = $this->chatMessage['created_at'];  
                    $isAgentRepondedFirstTime->save();
                    // create key for chat close by timeout
                    (new ExpireChatRepository)->storeChatKey($this->chatMessage['chat_channel_id']);
                    $this->updateFirstRespondedVisitorTime($this->chatMessage['created_at'], $this->chatMessage['chat_channel_id']);
                }
                
            }
            elseif($this->chatMessage['recipient'] == ChatMessage::RECIPIENT_AGENT)
            {
                $this->updateOrCreateResponseTime(
                    ['chat_channel_id' => $this->chatMessage['chat_channel_id']],
                    ['visitor_responded_at' => $this->chatMessage['created_at']]);
           
                $channel = ChatChannel::find($this->chatMessage['chat_channel_id']);
                $agent = $channel->agent;
                (new ExpireChatRepository)->updateChatKeyExipreTime($this->chatMessage['chat_channel_id']);
                if(!is_null($agent) && $agent->checkPermissionBySlug('chat-notifier'))
                {
                    
                    $delay      = $agent->getPermissionSetting('chat-notifier');
                    $delaySeconds = (empty($delay) ? 60 : $delay['hour'] * 3600 + $delay['minute'] * 60 + $delay['second']);

                    dispatch(new VisitorReplied($channel, $delaySeconds))->delay($delaySeconds);
                }
            }
        }

    }


    private function updateOrCreateResponseTime($whereCond, $data)
    {
        try {
            //Handling race condition  of updateOrCreate
            ChatChannelResponseTiming::updateOrCreate($whereCond, $data);
        } catch (\Exception $exception) {
            ChatChannelResponseTiming::where($whereCond)->update($data);
        }
    }

    
    private function updateFirstRespondedVisitorTime($respondedAt, $chatChannelId)
    {
        try {
            $channelName = ChatChannel::where('id', $chatChannelId)
                           ->value('channel_name');
            if ($channelName) {
                $channel = ChatChannel::where('channel_name', $channelName)
                                     ->whereNull('first_response_to_visitor')
                                     ->whereNull('root_channel_id')
                                     ->orderBy('id', 'ASC')
                                     ->first();
                info($channel);
                info($respondedAt);
                info($channel->created_at->timestamp);
                if($channel) {
                    $channel->first_response_to_visitor = $respondedAt;
                    $channel->waiting_time_for_visitor = $respondedAt - $channel->created_at->timestamp;
                    $channel->save();
                }
            }
        } catch (\Exception $exception) {
            
        }
    }

}
