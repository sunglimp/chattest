<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class SneakLog extends Model
{
    public $timestamps = false;
    
    protected $guarded = [];
    
    /**
     * Function to add Log of sneaking.
     * 
     */
    public static function addLog($parent_id, $user_id)
    {
        try {
            $logData = [
                'parent_id' => $parent_id,
                'user_id'   => $user_id,
                'sneak_start' => date('Y-m-d H:i:s')
            ];
            
            return self::create($logData);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Fucntion to update sneak logs.
     * 
     * @param integer $parent_id
     * @throws \Exception
     */
    public static function updateLog($parent_id)
    {
        try {
            $log_id = Session::get('sneak_id');
            self::whereNull('sneak_end')
            ->where('parent_id', $parent_id)
            ->where('id', $log_id)
            ->update(['sneak_end'=> date('Y-m-d H:i:s')]);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to get parent user of current sneak user.
     * 
     * @param Model $user
     * @return null|SneakLog
     */
    public static function getParentUser($user)
    {
        $user_id = $user->id;
        $log_id = Session::pull('sneak_id');
        if (!empty($log_id)) {
            return SneakLog::where('id', $log_id)->where('user_id', $user_id)->first();
        } else {
            return null;
        }
    }
}
