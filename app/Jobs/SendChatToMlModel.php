<?php

namespace App\Jobs;

use App\Facades\MlModelFacade;
use App\Http\Controllers\Api\V1\AgentController;
use App\Libraries\MlModelAPI;
use App\Models\ChatChannel;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendChatToMlModel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $agentId;
    private $channelId;

    public function __construct($agentId, $channelId)
    {
        //
        $this->agentId=$agentId;
        $this->channelId= $channelId;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        info("=================== In ML Model============== Agent ID :".$this->agentId. " Chat ID :".  $this->channelId);

        $data=ChatChannel::with('historyMessage')->where('id', $this->channelId)->first();
        $body= ['chat_channel_id'=>$data->channel_name];
        $body["transcript"]=[];
        foreach ($data->historyMessage as $k => $v) {
            $body["transcript"][$k]=json_decode($v->message);
            $body["transcript"][$k]->type=$v->recipient;
        }

        $user= User::find($this->agentId);

        $token=$user->getPermissionSetting('classified_chat');
        if (count($token)) {
            $token = $token['ml_model_token'];
            info("===========Token is :: " . $token . "=====================");
            try {
                $headers = [
                    'Authorization' => 'Token ' . $token,
                ];
                $url = config('mlmodel.api_url');

                info('ML Request initiated  URL ::' . $url . " Body :: ", $body);
                $data = MlModelFacade::request(MlModelAPI::POST, $url, $body, $headers)->getResponse();
                info("===========Response ======\n" . $data . "\n ==========Comming=========== ");
                $data = json_decode($data);

                $chatChanel = ChatChannel::where('channel_name', $data->chat_channel_id)->latest()->first();

                if (!empty($chatChanel)) {
                    $chatChanel->ticket_status = ChatChannel::CHAT_STATUS_PENDING;
                    $chatChanel->ticket_type = strtoupper($data->tag);
                    $chatChanel->save();
                    info("Channel pending status saved");

                }

            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
        } else {
            info("Token Not found  ");
        }

    }
}
