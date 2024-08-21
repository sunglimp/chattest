<?php

namespace App\Jobs;

use App\Models\DownloadTrack;
use App\Models\PermissionSetting;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Excel;
use App\Exports\ChatDownloadAgentWiseExport;
use App\Models\ChatMessage;

class ChatDownloadAgentWise implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $request;

    public function __construct($params = [])
    {
        $this->request = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fileName = $this->runSqlChatDownload($this->request);
        $this->sendChatDownloadEmail($this->request, $fileName);
    }


    private function runSqlChatDownload($params)
    {
        Log::debug("ChatDownloadAgentWise::Download file create process started");
        //Masking Identifier Permission check
        $identifier = 'identifier';
        $responder  = 'c.identifier';
        $organizationId = $this->request['organization_id'];
        $identifierPermission = false;
        if(checkIndentifierMaskPermission($params['user_id'])) {
            $identifier = 'mask_identifier(identifier)';
            $responder  = 'mask_identifier(c.identifier)';
            $identifierPermission = true;
        }
        //Masking Identifier Permission check

        // Customer information check
        $clientDisplaySetting = checkOrganizationChatLabel($params['user_id']);
        // Customer information check
        date_default_timezone_set($params['user_time_zone']);
        $startDate = Carbon::parse($params['start_date'])->timestamp;
        $endDate   = Carbon::parse($params['end_date']. ' 23:59:59')->timestamp;
        $agentIds = empty($params['reportee']) ? $this->getAgentsList($params) : [$params['reportee']];
        $fileName = 'chats-'. $organizationId . '-'. $params['key'] .  '-' . time().uniqid(rand()) . '.xlsx';
        $filePath = DownloadTrack::getFilePath($fileName);
        $system_timezone = config('app.timezone');

        $query = ChatMessage::getChatAgentWise($system_timezone, $params, $identifier, $responder, $startDate, $endDate, $agentIds);

        Excel::store(new ChatDownloadAgentWiseExport($this->request['organization_id'], $query, $clientDisplaySetting, $identifierPermission), $filePath, 'public');

        Log::debug("File created ".$filePath);
        Log::debug("ChatDownloadAgentWise::Download file create process completed");
        return $fileName;
    }

    private function getAgentsList($params)
    {
        if ($params['role_id'] == 2) {
            $userIds = User::where('organization_id', $params['organization_id'])
                    ->where('id', '!=', $params['user_id'])
                    ->pluck('id')->toArray();
        } else {
            $userIds = get_direct_reportees($params['user_id'], true);
            array_push($userIds, $params['user_id']);
        }
        return $userIds;
    }

    /**
     * Send mail notification for download file
     *
     * @param Request $request
     * @param string $fileName
     */
    private function sendChatDownloadEmail($request, $fileName)
    {
        try {
             Log::debug("ChatDownloadAgentWise::Download file mail send process started");
             $processed = 2;
             $organizationId = $this->request['organization_id'];
             $filePath = DownloadTrack::getFilePath($fileName);
             $fileStoragePath = public_path('storage'.'/'.$filePath);
             if(file_exists($fileStoragePath)){
                Log::debug('ChatDownloadAgentWise::File found on the path '.$fileStoragePath);
                 //get the organization mail settings
                $permissionData = PermissionSetting::getPermissionSettingData($request['organization_id'],config('constants.PERMISSION.EMAIL'));
                $settings = (isset($permissionData) && verifyEmailSetting(json_decode($permissionData->settings,true))) ? json_decode($permissionData->settings,true) : getApplicationEmailSetting();
                if(!empty($settings)) {
                     //get the organization mail settings
                     //$settings = json_decode($permissionData->settings,true);
                     $transport = new Swift_SmtpTransport($settings['host'], $settings['port']);
                     $transport->setUsername($settings['username']);
                     $transport->setPassword($settings['password']);
                     $transport->setEncryption($settings['encryption']?? null);
                     $mailer = new Swift_Mailer($transport);
                     $to = $request['user_email'];
                     $userName = $request['user_name'] ?? 'User';
                     $agentName = $request['agent_name'] ?? 'Agent';
                     $subject = "Surbo Chat's archive chat report download link by ". $request['start_date']." - ".  $request['end_date'];
                     $fileNameEncrypt = encrypt($fileName);
                     $url = route('agnet-wise-chat-download', $fileNameEncrypt);
                     Log::debug('ChatDownloadAgentWise::File download link is '.$url);
                     $from = $settings['from_email'];
                     $message   = (new Swift_Message($subject))
                        ->setFrom($from)
                        ->setTo($to)
                        ->setBody(view('chat.email.email-agent-wise', ['url' => $url, 'fileName' => $fileName, 'agentName' => $agentName, 'userName' => $userName])->render(),'text/html');
                        $mail = $mailer->send($message);
                        if($mail){
                            Log::debug('ChatDownloadAgentWise::Mail send successfully');
                            // set processed value to update in download tracks
                            $processed = 1;
                        }
                } else {
                    Log::debug("ChatDownloadAgentWise::Email setting is missing..!''.");
                    $processed = config('constants.DOWNLOAD_TRACKS.FAILURE');
                }
            }
           $downloadTrack= DownloadTrack::where('user_id', $request['user_id'])->where('params', $request['key'])->latest()->first();
           $downloadTrack->processed = $processed;
           $downloadTrack->save();

        } catch (\Exception $exception) {
            log_exception($exception);
        }
    }

}
