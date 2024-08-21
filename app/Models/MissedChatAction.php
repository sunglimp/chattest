<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Facades\SurboAPIFacade;
use App\Libraries\SurboAPI;
use App\Models\PermissionSetting;


class MissedChatAction extends Model
{
    
    protected $dateFormat = 'U';
    public $timestamps = true;
    protected $fillable = ['chat_channel_id','template_id','status'];

    public static function sendWAPush($chatChannelId)
    {
        $missedChatSettings = PermissionSetting::where('organization_id', Auth::user()->organization_id)->where('permission_id', config('constants.PERMISSION.MISSED-CHAT'))->select('settings')->first();
        try {
            $actionRec = MissedChatAction::where('chat_channel_id', $chatChannelId)->first();
            $settingData = json_decode($missedChatSettings->settings, true);
            $clientId = ChatChannel::find($chatChannelId)->client_id;

            $body = [
                    "bot_id" => $settingData['botId'],
                    "text" => "",
                    "mobileNumber" => Client::find($clientId)->identifier,
                    "service" => config('constants.WA_PUSH_SERVICE'),
                    "template_info" =>  $settingData['templateId']
                ];
            $headers = [
                    'Authorization' => 'Token ' . $settingData['token'],
            ];
            $url = rtrim($settingData['api']);

            
            SurboAPIFacade::request(SurboAPI::POST, $url, $body, $headers)->getResponse();
            $actionRec->template_id = $settingData['templateId'];
            $actionRec->save();
            return true;
        } catch (\Exception $e) {
            MissedChatAction::find($actionRec->id)->delete();
            \Log::error($e->getMessage());
            return false;
        }  
    }
}
