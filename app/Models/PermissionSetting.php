<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionSetting extends Model
{

    //
    protected $dateFormat = 'U';
    public $timestamps = true;
    protected $fillable = ['organization_id', 'permission_id', 'settings', 'created_at'];
//    protected $casts = [
//        'settings' => 'array'
//    ];

    public static function getPermissionSettingData($organizationId, $settingId)
    {
        $data = PermissionSetting::where('organization_id', $organizationId)->where('permission_id', $settingId)->first();
        return $data;
    }

    public static function updateSetting($organizationId, $settingValue, $permissionId)
    {

        return PermissionSetting::updateOrCreate(['organization_id' => $organizationId, 'permission_id' => $permissionId], ['settings' => $settingValue]);
       
    }
    
    public static function getTagSettings($organizationId, $permissionId)
    {
        $settings['tag_required'] = false;
        $tagSettings = self::getPermissionSettingData($organizationId, $permissionId);
        if($tagSettings) {
            $settings = json_decode($tagSettings->settings, true);
        }
        return $settings;
    }
    
    /**
     * Function for get session timeout, if it not set it will return default
     * 
     * @param int $organizationId
     * @param int $permissionId
     * @return mixed
     */
    public static function getSessionTimeoutSettings($organizationId, $permissionId)
    {
        $sessionTime = config('constants.LAST_ACTIVITY_SESSION_TIME');
        $sessionSettings = self::getPermissionSettingData($organizationId, $permissionId);
        if($sessionSettings) {
            $settings = json_decode($sessionSettings->settings);
            $sessionTime = ($settings ) ? (($settings->hour*60)+$settings->minute+ (($settings->second > 0) ? ($settings->second/60) : 0)) : $sessionTime;
        }
        return $sessionTime;
    }
    
    /**
     * Get chat timeout value on seconds 
     * 
     * @param int $organizationId
     */
    public function getChatTimeoutSettings($organizationId)
    {
        $expireTime = config('chat.chat_default_expire_time')*60;
        $permissionSettings = self::where('permission_id', config('constants.PERMISSION.TIMEOUT'))
                ->where('organization_id', $organizationId)->first();
        if($permissionSettings) {
            $settings = $permissionSettings->settings ? json_decode($permissionSettings->settings) : [];
            $expireTime = ((int)$settings->hour*3600) +  ((int)$settings->minute*60) + ((int)$settings->second); 
        }
        return $expireTime;
    }
}
