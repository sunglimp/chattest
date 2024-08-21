<?php

namespace App\Http\Utilities;

use Illuminate\Support\Facades\Redis;
use App\Models\PermissionSetting;

class CommonHelper
{

    public static function getOrganizationAutoTransfer($organizationId){

      $count = config('constants.AUTO_CHAT_TRANSFER_LIMIT');
      $redisCount = Redis::get('organization_'.$organizationId.'_auto_transfer');
      if($redisCount){
          return $redisCount;
      }
      else{
          $settingData = PermissionSetting::getPermissionSettingData($organizationId, config('constants.PERMISSION.AUTO-CHAT-TRANSFER'));

          $data = json_decode($settingData->settings);
          if(isset($data->transfer_limit)){
              $count = $data->transfer_limit;
          }
      }
      return $count;

    }

    public static function formatOfflineQuery($query, $identifier, $seperator = ','){

        $decode_query = json_decode($query, true)??[];
        $format_query = [];
        foreach($decode_query as $data){
         if($data['message_type'] == 'BOT'){

            $format_query[]= "BOT: ".$data['response_text'];
        }
        if($data['message_type'] == 'VISITOR'){

            $format_query[]= $identifier.": ".$data['response_text'];
        }
        }
        $format_query = implode($format_query, $seperator);
        return $format_query;
    }


}
