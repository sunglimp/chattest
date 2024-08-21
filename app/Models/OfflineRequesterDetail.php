<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OfflineForm;
use App\Agent;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Auth;
use Carbon\Carbon;


class OfflineRequesterDetail extends Model
{
    protected $dateFormat = 'U';
    public $timestamps = true;

    protected $fillable = [
        'organization_id','group_id','client_info','transcript','organization_id','source_type'
    ];

    public function offlineQuery()
    {
        return $this->hasOne(OfflineForm::class, 'offline_requester_detail_id');
    }
    public function group()
    {
        return $this->hasOne(Group::class, 'id','group_id');
    }

    public function getOfflineQuery($organizationId, $offlineQueriesType, $startDate, $endDate, $status)
    {
        $query = self::select('offline_requester_details.id', 'group_id', 'source_type', 'client_info->>identifier as mobile','offline_requester_details.created_at', 'status')
            ->with('offlineQuery','group')
            ->where(['offline_requester_details.organization_id' => $organizationId])
            ->whereBetween('offline_requester_details.created_at', [$startDate, $endDate]);
            if ($status != '') {
            $query->where('status', $status);
            }
        if (!Gate::allows('all-admin') && $offlineQueriesType==config('constants.OFFLINE_QUERIES_TYPE.GROUP')) {
            $query->whereIn('group_id', Agent::groupIds(Auth::user()->id));
        }
        $offlineQuery = $query->latest();
        return $offlineQuery;

    }

    public function changeStatus($requestId, $agentId, $status)
    {
       self::where('id', $requestId)
        ->update([
            'agent_id' => $agentId,
            'status' => $status
         ]);
    }

    public static function getDownloadSql($organizationId, $userId, $systemTimezone, $identifier, $startDate, $endDate, $status, $userTimezone, $showOnlyForGroup)
    {
        $query =  DB::table('offline_requester_details AS ord')->select('g.name as groupName', 'source_type as sourceType', DB::raw("$identifier as identifier"), 'client_query as ClientQuery', DB::raw('DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(ord.created_at),"' . $systemTimezone .'", "' . $userTimezone . '"), \'%d/%m/%Y\') AS Date'), DB::raw('DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(ord.created_at),"' . $systemTimezone . '", "'. $userTimezone . '"), \'%r\') AS Time'), DB::raw('(CASE WHEN ord.status =1 THEN "Contact Customer" WHEN ord.status=2 THEN "Customer Contacted" WHEN ord.status =3 THEN "Rejected" END) as status'))
            ->join('groups as g', 'ord.group_id', '=', 'g.id')
            ->leftJoin('offline_forms as of', 'ord.id', '=', 'of.offline_requester_detail_id')
            ->where('ord.created_at', '>=',  $startDate)
            ->where('ord.created_at', '<=', $endDate);
        if ($showOnlyForGroup) {
            $query->whereIn('group_id', Agent::groupIds($userId));
        } else {
            $query->where('ord.organization_id', $organizationId);
        }
        if(!empty($status)) {
            $query->where('ord.status', $status);
        }
        return $query->get();
    }

    public static function getOfflineQueryById($id)
    {
        $query = self::select('offline_requester_details.*', 'offline_requester_details.client_info->>identifier as mobile')
            ->with('offlineQuery','group')
            ->where(['offline_requester_details.id' => $id]);
        return $query->latest()->first();
    }
}
