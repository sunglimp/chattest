<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOnlineStatus extends Model
{
    
    const STATUS_ONLINE = 1;
    const STATUS_OFFLINE = 0;
    
    protected $dateFormat = 'U';
    
    protected $fillable = ['user_id', 'status', 'created_at'];
    
    public function scopeOnline($query)
    {
        return $query->where('status', self::STATUS_ONLINE);
    }
    
    public function scopeOffline($query)
    {
        return $query->where('satus', self::STATUS_OFFLINE);
    }
    
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
    
    public static function changeStatus($userId, $status)
    {
        $userOnlineStatus = self::where('user_id', $userId);
        $userOnlineStatus->status = $status;
        $userOnlineStatus->save();
    }
}
