<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Models\ChatChannel;

class ChatChannelResponseTiming extends Model
{
    public $timestamps = false;
    protected $fillable = ['chat_channel_id', 'visitor_responded_at', 'agent_responded_at'];
    
    public function chatChannel()
    {
        return $this->belongsTo(ChatChannel::class,'chat_channel_id','id');
    }
    
    /**
     * Function to get average first response time.
     * 
     * @param string $now
     * @param array $agents
     * @throws Exception
     */
    public static function getAverageFirstResponseTime($now, $agents)
    {
        try {
            is_valid_date($now);
            User::select('users.id  as agent_id', 'users.organization_id', 'chat_channels.group_id',
                DB::raw('avg(agent_first_responded_at-chat_channels.agent_assigned_at) as avg_first_response_time')
            )->join('chat_channels', function($query) use($now){
                $query->on('chat_channels.agent_id', '=', 'users.id')
                ->where(DB::raw('DATE(FROM_UNIXTIME(chat_channels.created_at))'), $now)
                ->whereNotNUll('accepted_at');
            })->leftJoin('chat_channel_response_timings', 'chat_channel_response_timings.chat_channel_id', '=', 'chat_channels.id')
            ->whereNotIn('users.role_id', config('config.ADMIN_ROLE_IDS'))
            ->whereIn('users.id', $agents)
            ->withTrashed()// Alreday filtered 
            ->groupBy('users.id')
            ->get()
            ->summarize($now, 'avg_first_response_time');
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    
}
