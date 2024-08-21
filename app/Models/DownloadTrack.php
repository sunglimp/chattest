<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PermissionSetting;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Illuminate\Support\Facades\Log;

class DownloadTrack extends Model
{
    protected $dateFormat = 'U';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'params',
        'processed',
    ];

    public static function allowProcess($userId, $key)
    {

        $downloadRec = self::where('user_id', $userId)->where('params', $key)->where('processed', 0)->first();
        if ($downloadRec) {
            return false;
        } else {
            self::create([
                'user_id' => $userId,
                'params'  => $key,
                'processed' => 0
            ]);
            return true;
        }

    }

    public static function setStatus($userId, $key, $processed)
    {
        $downloadTrack= DownloadTrack::where('user_id', $userId)->where('params', $key)->latest()->first();
        $downloadTrack->processed = $processed;
        $downloadTrack->save();
    }

    /**
     * Function for download file path
     *
     * @param int $organizationId
     * @param string $fileName
     * @return type
     */
    public static function getFilePath($fileName)
    {
        return config('config.export_location.agent_chat_downloads').'/'.$fileName;
    }

    public static function sendDownloadLinkEmail($organizationId, $userId, $key, $filePath, $to, $from, $subject, $content, $className)
    {
        $fileStoragePath = public_path('storage' . '/' . $filePath);
        if(file_exists($fileStoragePath)){
            Log::debug($className . '::File found on the path '.$fileStoragePath);
            //get the organization mail settings
            $permissionData = PermissionSetting::getPermissionSettingData($organizationId, config('constants.PERMISSION.EMAIL'));
            $settings = (isset($permissionData) && verifyEmailSetting(json_decode($permissionData->settings,true))) ? json_decode($permissionData->settings,true) : getApplicationEmailSetting();
            if(!empty($settings)) {
                //get the organization mail settings
                //$settings = json_decode($permissionData->settings,true);
                $transport = new Swift_SmtpTransport($settings['host'], $settings['port']);
                $transport->setUsername($settings['username']);
                $transport->setPassword($settings['password']);
                $transport->setEncryption($settings['encryption']?? null);
                $from = $settings['from_email'];
                $mailer = new Swift_Mailer($transport);
                $message   = (new Swift_Message($subject))
                ->setFrom($from)
                ->setTo($to)
                ->setBody($content,'text/html');
                $mail = $mailer->send($message);
                if($mail){
                    Log::debug($className . '::Mail sent successfully');
                    // set processed value to update in download tracks
                    $processed = config('constants.DOWNLOAD_TRACKS.SUCCESS');
                }
            } else{
                Log::debug("OfflineQueryDownload::Email setting is missing..!''.");
                $processed = config('constants.DOWNLOAD_TRACKS.FAILURE');
            }
        }
        self::setStatus($userId, $key, $processed);
    }
}
