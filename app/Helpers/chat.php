<?php
use App\Events\InformQueueCountPrivateChannel;
use App\Jobs\SendChatNotification;
use App\Models\ChatChannel;
use App\Http\Utilities\CommonHelper;
use App\Http\Controllers\ChatController;
use Carbon\Carbon;

/**
 * Chat Helpers
 */
if (!function_exists('queue_chat_count')) {
    function queue_chat_count($groupId)
    {
        try {
            $agentsQueueInfo = ChatChannel::getQueueCount($groupId);
            if (!empty($agentsQueueInfo)) {
                foreach ($agentsQueueInfo as $agentId => $count) {
                    broadcast(new InformQueueCountPrivateChannel([
                        'event' => 'chat_queue_count',
                        'agent_id' => $agentId,
                        'queue_count' => $count
                    ]))->toOthers();
                }
            }
        } catch (\Exception $exception) {
            log_exception($exception);
        }
    }
}


/**
 * Function to send chat push notification to mobile devices
 */
if (!function_exists('send_chat_notification')) {
    function send_chat_notification($agentId, $event, $options = [])
    {
        try {
            Log::debug("SendChatNotification::Placed " . $event);
            SendChatNotification::dispatch($agentId, $event, $options)->onQueue(config('chat.queues.chat_notification'));
        } catch (\Exception $exception) {
            log_exception($exception);
        }
    }
}

/**
 * Function to format offline query data for email
 */
if (!function_exists('formatOfflineQueryEmailData')) {
    function formatOfflineQueryEmailData($offlineQuery, $organizationTimezone)
    {
        try {
            $identifier   = $offlineQuery->mobile ?? '';
            if (isset($offlineQuery->offlineQuery->client_query) && !empty($offlineQuery->offlineQuery->client_query && $offlineQuery->source_type=='whatsapp')) {
                $client_query = CommonHelper::formatOfflineQuery($offlineQuery->offlineQuery->client_query, $identifier, '#');
                $client_query = explode('#', $client_query);
                $client_query = array_map(function($val) use($identifier) {
                        return !empty($identifier) ? str_replace($identifier, 'CUSTOMER', $val) : 'CUSTOMER'.$val;
                },$client_query);
                $client_query = implode('<br>',$client_query);
            } else {
                $client_query = $offlineQuery->offlineQuery->client_query;
            }
            return [
                'group_name'   => $offlineQuery->group->name ?? '',
                'source_type'  => $offlineQuery->source_type ?? '',
                'identifier'   => $identifier,
                'client_query' => $client_query ?? '',
                'status'       => ChatController::STATUS[$offlineQuery->status],
                'datetime'     => Carbon::createFromTimestamp($offlineQuery->created_at->timestamp, $organizationTimezone)->format('Y-m-d H:i'),
            ];
        } catch (\Exception $exception) {
            log_exception($exception);
        }
    }
}