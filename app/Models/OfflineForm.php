<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OfflineRequesterDetail;
use Illuminate\Support\Facades\DB;

class OfflineForm extends Model
{
    protected $dateFormat = 'U';
    public $timestamps = true;
    protected $fillable = [
        'organization_id','identifier','client_query','offline_requester_detail_id'
    ];
    
    /**
     * Get the user that owns the phone.
     */
    public function offlineRequester()
    {
        return $this->belongsTo(OfflineRequesterDetail::class, 'offline_requester_detail_id');
    }
    
    public function getOfflineQuery($organizationId)
    {
        return self::with('offlineRequester')
            ->get();
            
    }
    
    /**
     * Function for summarize offline queries details based on organization as per the date 
     * 
     * @param date $now
     */
    public static function getOfflineQueryDetails($now)
    {
        try {
            self::select('organization_id', DB::raw('COUNT(client_query) AS count_offline_query'))
                ->where(DB::raw('DATE(FROM_UNIXTIME(created_at))'), $now)
                ->groupBy('organization_id')
                ->get()
                ->summarize($now, 'count_offline_query');
        } catch (\Exception $exception) {
            log_exception($exception);
        }
    }
}
