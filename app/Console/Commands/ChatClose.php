<?php

namespace App\Console\Commands;

use App\Models\OrganizationRolePermission;
use App\Models\Permission;
use App\Models\PermissionSetting;
use Illuminate\Console\Command;
use App\Models\ChatChannel;
use App\Models\ChatChannelResponseTiming;
use App\Events\InformVisitorChannel;
use function GuzzleHttp\json_decode;
use Illuminate\Support\Facades\DB;

class ChatClose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:close';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used for close the chat';

    public function handle()
    {
        info('===========TimeOut Chat-Close=================');

        $start = microtime(true);
        $data=ChatChannel::select('chat_channels.*', 'chat_channel_response_timings.visitor_responded_at','chat_channel_response_timings.agent_first_responded_at')
            ->join('chat_channel_response_timings', 'chat_channels.id', '=', 'chat_channel_response_timings.chat_channel_id')
            ->whereNotNull('agent_responded_at')
            ->activeSubscribers()
//            ->where('status',ChatChannel::CHANNEL_STATUS_PICKED)
//            ->whereNotNull('chat_channel_response_timings.visitor_responded_at')
//            ->where(DB::raw('chat_channel_response_timings.visitor_responded_at-chat_channel_response_timings.agent_responded_at '),'<',0)
            ->with('group.organization.timeoutSettings')
            ->get();
        $organizationPermission= OrganizationRolePermission::where('permission_id', config('constants.PERMISSION.TIMEOUT'))
            ->pluck('organization_id');

        $end = microtime(true) - $start;
        foreach ($data as $chatChanelData) {
            if ($organizationPermission->search($chatChanelData->group->organization->id)) {
                
                $vra = $chatChanelData->visitor_responded_at;
                info("vra====================".$vra);
                $visitorRespondedAt = empty($vra) ? 0 : $vra;
                $timeout = (json_decode($chatChanelData->group->organization->timeoutSettings->settings));
                $timeoutInSecond = ($timeout->hour * 3600) + ($timeout->minute * 60) + ($timeout->second);
                $responsetimediff = now()->timestamp - $vra;
                info("timeout====================".$timeoutInSecond);
                info("resdiff====================".$responsetimediff);
                if ((empty($vra) && ((now()->timestamp-$chatChanelData->agent_first_responded_at) >$timeoutInSecond))
                   || (!empty($vra) && $responsetimediff >= $timeoutInSecond)) {
                    info("chat " . $chatChanelData->id . ' is being closed');
                    if ($chatChanelData->close(ChatChannel::CHANNEL_STATUS_TERMINATED_BY_VISITOR , true)) {
                        info("chat " . $chatChanelData->id . ' is closed');
                        broadcast(new InformVisitorChannel([
                            'event' => 'chat_timeout',
                            'channel_name' => $chatChanelData->channel_name,
                        ]))->toOthers();
                    } else {
                        info("chat " . $chatChanelData->id . ' could not be closed');
                    }
                }

            }
        }



    }
}
