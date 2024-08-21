<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalCommentChannel extends Model
{
    public $timestamps = false;
    public $guarded = [];
    /**
     * Function to remove mapping of internal comments with chats.
     * 
     * @param integer $chatId
     * @param integer $agentId
     * @throws \Exception
     */
    public static function deleteInternalComments($chatId, $agentId)
    {
        try {
            return self::where('chat_channel_id', $chatId)
                  ->where('internal_agent_id', $agentId)
                  ->delete();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to insert internal comments mapping with chat.
     * 
     * @param integer $chatId
     * @param integer $agentId
     */
    public static function addInternalCommentChannel($chatId, $agentId)
    {
        try {
            $record = self::firstOrCreate(['chat_channel_id' => $chatId, 'internal_agent_id' => $agentId]);
            if ($record->wasRecentlyCreated) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to get channelIds by agent id.
     * 
     * @param integer $agentId
     * @throws \Exception
     * @return array channelIds
     */
    public static function getChannelIdsByAgentId($agentId)
    {
        try {
            $channelId = InternalCommentChannel::where('internal_agent_id', $agentId)->pluck('chat_channel_id')->all();
            return $channelId;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
