<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\User;
use App\Models\DownloadTrack;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Jobs\OfflineQueryDownload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class OfflineQueryControlller extends BaseController
{

    /**
     * @api {get} /offlineQueries/download   Download Offline Queries
     * @apiVersion 1.0.0
     * @apiName Download Offline Queries
     * @apiGroup Offline Query
     *
     * @apiParam {String} start_date start date dd-mm-yyyy.
     * @apiParam {String} end_date end date.
     * @apiParam {integer} status 1-Contact Customer,2-Customer Contacted, 3- Rejected.
     *
     * @apiHeader {String} Authorization API token
     * @apiHeader {String} Content-Type Content type of the payload
     *
     * @apiHeaderExample {json} Content-Type:
     *     {
     *         "Content-Type" : "application/json"
     *     }
     * @apiHeaderExample {json} Authorization:
     *     {
     *         "Authorization" : "Bearer  l39I1Pyerw0DhDUTviio"
     *     }
     *
     * @apiSuccessExample Offline Query data:
     *     HTTP/1.1 200 OK
     *    {
	"status": true,
	"message":"Request is under process. You will receive the email shortly."
     *  "data":[]
     *    }
     *
     * * @apiSuccessExample Already Download In Process:
     *     HTTP/1.1 200 OK
     *    {
	"status": false,
	"message":"Your previous request is still under process, Please try after sometime."
     *  "data":[]
     *    }
     *
     */


public function download(Request $request)
    {

        $organization    = Auth::user()->organization;
        $organizationId = $organization->id;
        $startDate = $request->input('start_date');
        $endDate= $request->input('end_date');
        $status  = $request->input('status');
        $key = 'offline-query-' . $startDate . '-' . $endDate;
        if (!$status) {
            $key .= '-' . $status;
        }
        if (DownloadTrack::allowProcess(Auth::user()->id, $key)) {
            $params = [
                'start_date' =>  $startDate,
                'end_date'   =>  $endDate,
                'status'  =>  $status,
                'organization_id' => $organizationId,
                'user_time_zone' => Auth()->user()->timezone,
                'user_email'     => Auth()->user()->email,
                'user_id' => Auth()->user()->id,
                'key' => $key,
                'user_name'=> Auth()->user()->name,
                'admin' => Gate::allows('all-admin')
                ];
            OfflineQueryDownload::dispatch($params)
                            ->onQueue('offline-query-download');
            return $this->successResponse(default_trans($organizationId . '/offline_queries.success_messages.download', __('default/offline_queries.success_messages.download')));
        } else {
            return $this->failResponse(default_trans($organizationId . '/offline_queries.fail_messages.download', __('default/offline_queries.fail_messages.download')));
        }
    }
}
