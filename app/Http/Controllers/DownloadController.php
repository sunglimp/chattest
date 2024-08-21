<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\DownloadTrack;

class DownloadController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        
    }
    
    /**
     * Download Agent wise chat file
     * 
     * @param int $organizationId
     * @param string $fileName Encrypted filename
     * @return Response
     */
    public function downloadAgentWiseChatFile($fileName){
        try {
            $fileNameDecrypt = decrypt($fileName);
            $filePath = DownloadTrack::getFilePath($fileNameDecrypt);
            $path = Storage::path($filePath);
            if(file_exists($path)){
                return response()->download($path)->deleteFileAfterSend(true);
            } else {
                Log::debug("DownloadController::downloadAgentWiseChatFile the file is not found ".$path);
                abort(404, 'Sorry, the file you are looking for could not be found.');
            }
        } catch (\Exception $exception) {
            log_exception($exception);
            abort(404);
        }
        
    }

    public function downloadOfflineQueryFile($fileName)
    {
        return $this->downloadFile(config('config.export_location.offline_query_downloads'), $fileName, 'Offline Query');
    }

    private function downloadFile($location, $fileName, $type)
    {
        try {
            $fileNameDecrypt = decrypt($fileName);
            $filePath = $location.'/'.$fileNameDecrypt;
            $path = Storage::path($filePath);
            if(file_exists($path)){
                return response()->download($path)->deleteFileAfterSend(true);
            } else {
                Log::debug("DownloadController::$type the file is not found ".$path);
                abort(404, 'Sorry, the file you are looking for could not be found.');
            }
        } catch (\Exception $exception) {
            log_exception($exception);
            abort(404);
        }
        return false;
    }
}
