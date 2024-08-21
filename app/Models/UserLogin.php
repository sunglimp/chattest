<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\User;

class UserLogin extends Model
{
    protected $dateFormat = 'U';
    public $timestamps = true;
    
    protected $fillable = [
        'user_id','in_time'
    ];
    
    public static function updateOfflineTime($agentId)
    {
        
        $userLogoutTime = UserLogin::whereNull('out_time')
                                    ->where('user_id', $agentId)
                                    ->orderBy('id', 'desc')->first();
        if ($userLogoutTime) {
            $userLogoutTime->out_time = Carbon::now()->timestamp;
            $userLogoutTime->save();
        }
    }
    
    /**
     * Function to get average online duration.
     *
     * @param string $now
     * @param array $agents
     * @throws Exception
     * @error need to sum duration
     */
    public static function getAverageOnlineDuration($now, $agents=[])
    {
        try {
            is_valid_date($now);
            
            User::select(
                'users.id  as agent_id',
                'users.organization_id',
                DB::raw('sum(user_logins.out_time - user_logins.in_time) AS avg_online_duration')
            )->join('user_logins', function ($query) use ($now) {
                $query->on('user_logins.user_id', '=', 'users.id')
                ->where(DB::raw('DATE(FROM_UNIXTIME(user_logins.updated_at))'), $now);
            })
            ->whereNotIn('users.role_id', config('config.ADMIN_ROLE_IDS'))
            ->whereIn('users.id', $agents)        
            ->groupBy('users.id')
            ->get()
            ->summarize($now, 'avg_online_duration');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to get online duration.
     *
     * @param string $now
     * @param $agents;
     * @throws \Exception
     */
    public static function getOnlineDuration($now, $agents=[])
    {
        try {
            is_valid_date($now);
            $data = self::select(DB::raw('sum(out_time - in_time) as online_duration'), 'organization_id', 'user_id as agent_id')
                ->join('users', 'users.id', '=', 'user_logins.user_id')
                ->where(DB::raw('DATE(FROM_UNIXTIME(user_logins.updated_at))'), $now)
                ->whereIn('user_logins.user_id', $agents)
                ->groupBy(DB::raw('DATE(FROM_UNIXTIME(user_logins.updated_at))'), 'user_logins.user_id')
                ->get()
                ->summarize($now, 'online_duration');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to get online duration.
     *
     * @param integer $loggedInUserId
     * @throws \Exception
     */
    public static function getUserOnlineDuration($loggedInUserId)
    {
        try {
            $user = User::find($loggedInUserId);
            $currentDate = (Carbon::now($user->timezone)->format('Y-m-d'));
            $now= Carbon::now()->timestamp;
            $data = self::select(DB::raw('sum(IF(out_time IS NOT NULL, (out_time - in_time),('.$now.' - in_time))) as online_duration'))
            ->where(DB::raw('DATE(FROM_UNIXTIME(user_logins.updated_at))'), $currentDate)
                 ->where('user_id', $loggedInUserId)
                ->groupby('user_id')
                 ->first();


            if (!empty($data->online_duration)) {
                return convert_average_time($data->online_duration, true, true);
            } else {
                return convert_average_time(0, true, true);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
