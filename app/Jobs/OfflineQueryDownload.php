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

use Excel;
use App\Exports\OfflineQueryDownloadExport;
use App\Models\OfflineRequesterDetail;

class OfflineQueryDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $request;
    private $filePath;

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
        $fileName = $this->runOfflineQueryDownload($this->request);
        $this->sendOfflineQueryDownloadEmail($this->request, $fileName);
    }


    private function runOfflineQueryDownload($params)
    {
        Log::debug("OfflineQueryDownload::Download file create process started");
        //Masking Identifier Permission check
        $identifier = '`client_info`->>\'$."identifier"\'';
        $organizationId = $this->request['organization_id'];
        if(checkIndentifierMaskPermission($params['user_id'])) {
            $identifier = "mask_identifier($identifier)";
        }
        date_default_timezone_set($params['user_time_zone']);
        $startDate = Carbon::parse($params['start_date'])->timestamp;
        $endDate   = Carbon::parse($params['end_date']. ' 23:59:59')->timestamp;
        $fileName = 'offline-query-'. $organizationId . '-'. $params['start_date'] . '-' . $params['end_date'] .  '-' . time().uniqid(rand()) . '.xlsx';
        $this->filePath = config('config.export_location.offline_query_downloads').'/'.$fileName;
        $offlineSettings = PermissionSetting::where('organization_id', $organizationId)->where('permission_id', config('constants.PERMISSION.OFFLINE-FORM'))->select('settings->offline_query_type as offline_query_type')->first();
        $offlineQueriesType = json_decode($offlineSettings->offline_query_type) ?? config('constants.OFFLINE_QUERIES_TYPE.ORGANIZATION');
        $showOnlyForGroup = false;
        if (!$params['admin'] && $offlineQueriesType==config('constants.OFFLINE_QUERIES_TYPE.GROUP'))
        {
            $showOnlyForGroup = true;
        }
        Excel::store(new OfflineQueryDownloadExport($organizationId, $params['user_id'], $showOnlyForGroup, $identifier, $startDate, $endDate, $params['status'], $params['user_time_zone']), $this->filePath, 'public');
        
        Log::debug("File created ". $this->filePath);
        Log::debug("OfflineQueryDownload::Download file create process completed");
        return $fileName;
    }
    
    /**
     * Send mail notification for download file
     * 
     * @param Request $request
     * @param string $fileName
     */
    private function sendOfflineQueryDownloadEmail($request, $fileName)
    {
        try {
            Log::debug("OfflineQueryDownload::Download file mail send process started");
            $processed = config('constants.DOWNLOAD_TRACKS.FAILURE');
            $organizationId = $this->request['organization_id'];
            $userName = $request['user_name'] ?? 'User';
            $subject = "Surbo Chat's offline query report download link by ". $request['start_date']." - ".  $request['end_date'];
            $fileNameEncrypt = encrypt($fileName);
            $url = route('offline-query-download', $fileNameEncrypt);
            Log::debug('OfflineQueryDownload::File download link is '.$url);
            $content = view('chat.email.email-offline-query', ['url' => $url, 'fileName' => $fileName,'userName' => $userName])->render();
            DownloadTrack::sendDownloadLinkEmail($organizationId, $request['user_id'], $request['key'], $this->filePath, $request['user_email'], $request['user_email'], $subject, $content, 'OfflineQueryDownload');
        } catch (\Exception $exception) {
            log_exception($exception);
        }
    }

}
