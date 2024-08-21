<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\AgentController;
use App\Models\ChatMessage;
use App\User;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use App\Facades\MlModelFacade;
use App\Libraries\MlModelAPI;

class SurboSendApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:message-send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {


//        $obj= new AgentController();
//        $permission=$obj->permissions(4);
////        dd(($permission->original['data']['classified_chat']));
//        dd($permission->original['data']['settings']['classified_chat']->ml_model_token);
        $user= User::find(4);
        dd($user->getPermissionSetting('classified_chat')['ml_model_token']);


        $chatMessage = ChatMessage::find(988);
        $chatChannel = $chatMessage->chatChannel;
        $chatChannel->end_point = 'https://bf15f537.ngrok.io/api/v2/chatbot/livechat/';
        $chatChannel->token = '5ca1ec1eae637b13b49a6ac5';
        if (!empty($chatChannel->end_point) && !empty($chatChannel->token)) {
            $message = json_decode($chatMessage->message, true);

            $body =[
                'message' => $message,
                'recipient' => $chatMessage->recipient,
                'message_type' => $chatMessage->message_type,
                'sender_display_name' => request()->get('sender_display_name'),
                'attachment_path' =>  !empty($message['path']) ? url($message['path']) : null
            ];

            $this->info(json_encode($body));

            $client = new Client();

            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $chatChannel->token,
            ];

            try {
                info('request initiated to ' . $chatChannel->end_point. ' with', $body);
                $request = $client->post($chatChannel->end_point, [
                    'headers' => $headers,
                    'json'=> $body
                ]);

                info('response body', json_decode($request->getBody()->getContents(), true));

            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }

        }
    }
}
