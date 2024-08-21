<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;

class LoginHistory extends Model
{

    protected $dateFormat = 'U';
    public $timestamps    = false;
    protected $fillable = ['user_id', 'login_time', 'ip_detail', 'device_detail', 'device_id', 'device_type', 'device_token'];

    public static function getLoggedInUserList($startDate, $endDate, $organizationId, $userIds)
    {

        $userList = LoginHistory::select('login_histories.user_id as id', 'users.name as name', DB::raw('count(login_histories.user_id) as login_count'), DB::raw('sum(login_histories.duration) as duration'), DB::raw('sum(blocked_client_count) as blocked_clients'), DB::raw('sum(chat_count) as chat_count'), 'users.last_login', 'roles.name as role_name')
                ->join('users', function ($query) {
                    $query->on('users.id', '=', 'login_histories.user_id');
                })
                ->join('roles', function ($query) {
                    $query->on('users.role_id', '=', 'roles.id');
                });
        if ($organizationId) {
            $userList->where('users.organization_id', $organizationId);
        }
        if (count($userIds) > 0) {
            $userList->whereIn('users.id', $userIds);
        }
        $userList->where(DB::raw("date(FROM_UNIXTIME(login_histories.login_time))"), '>=', $startDate)
                ->where(DB::raw("date(FROM_UNIXTIME(login_histories.logout_time))"), '<=', $endDate)
                ->groupBY('login_histories.user_id');
        $list = $userList->get();

        return $list;
    }

    public static function getUserHistoryList($startDate, $endDate, $userId)
    {
        $userList = LoginHistory::select('id','ip_detail', 'device_detail', 'duration', 'login_time', 'logout_time', 'chat_count', 'blocked_client_count as blocked_clients',
                                         DB::raw("date_format(FROM_UNIXTIME(login_time),'%d-%m-%Y') as lgn_dte"),
                                         DB::raw("date_format(FROM_UNIXTIME(logout_time),'%d-%m-%Y') as lgt_dte"),
                                         DB::raw("date_format(FROM_UNIXTIME(logout_time),'%H:%i:%s') as lgt_time"),
                                         DB::raw("date_format(FROM_UNIXTIME(login_time),'%H:%i:%s') as lgn_time"))
                ->where('user_id', $userId)
                ->where(DB::raw("date(FROM_UNIXTIME(login_time))"), '>=', $startDate)
                ->where(DB::raw("date(FROM_UNIXTIME(logout_time))"), '<=', $endDate)
                ->orderBy('id','desc')
                ->get();
//                 $userList->map(function($val, $key) use($timezone){
//                     $val->lgn_dte = Carbon::createFromTimestamp($val->login_time, $timezone)->format('Y-m-d');
//                     $val->lgt_dte = Carbon::createFromTimestamp($val->logout_time, $timezone)->format('Y-m-d');
//                     $val->lgt_time = Carbon::createFromTimestamp($val->login_time, $timezone)->format('H:i:s');
//                     $val->lgn_time = Carbon::createFromTimestamp($val->logout_time, $timezone)->format('H:i:s');
//                 });

        return $userList;
    }

    public static function updateLogoutTime($id, $roleId)
    {
        $chatCount     = 0;
        $blockedCount  = 0;
        $logoutHistory = LoginHistory::where('user_id', $id)->where('logout_time', null)->orderBy('id', 'desc')->first();
        $logoutTime    = \Carbon\Carbon::now()->timestamp;
        if($logoutHistory){
        if ($roleId != config('constants.user.role.super_admin') || $roleId != config('constants.user.role.admin')) {
            $chatCount    = \App\Models\ChatChannel::where('agent_id', $id)->whereBetween('agent_assigned_at', [$logoutHistory->login_time, $logoutTime])->count();
            $blockedCount = \App\Models\BannedClient::where('banned_by', $id)->whereBetween('banned_at', [$logoutHistory->login_time, $logoutTime])->count();
        }

            $logoutHistory->logout_time          = $logoutTime;
            $logoutHistory->chat_count           = $chatCount;
            $logoutHistory->blocked_client_count = $blockedCount;
            $logoutHistory->duration             = $logoutTime - $logoutHistory->login_time;
            $logoutHistory->save();
        }

        return $logoutHistory;
    }

    /**
     * Function for get users logout with in configured time
     * @param int $execute_time Time frequency in minutes
     *
     * @return array
     */
    public static function getUsersLogoutWithInTime($execute_time='')
    {
        $time = ($execute_time!='') ? $execute_time : config('config.SUMMARY_EXECUTE_TIME');
        return self::join('users', 'login_histories.user_id', 'users.id')
            ->where(function($query) use ($time){
                $query->where(function($query1) use ($time) {
                    $query1->where(DB::raw('TIMESTAMPDIFF(MINUTE, FROM_UNIXTIME(logout_time), NOW())'), '<=', $time);
                    }

                );
                $query->orWhere(function($query2) {
                    $query2->whereNull('logout_time')->where('users.is_login', 1);
                });
            })
            ->whereNull('users.deleted_at')->whereNotIn('users.role_id', config('config.ADMIN_ROLE_IDS'))
            ->groupBy('user_id')->pluck('user_id');
    }
}
