<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;
use App\Events\InformVisitorChannel;
use App\Models\ChatChannel;
use App\Models\OrganizationRolePermission;
use Illuminate\Support\Facades\Redis;

class ExpireChatRepository 
{
    private static $organizationId;
    private static $redis;
    
    public function __construct()
    {
         //Create a new instance for exipre chat
        self::$redis = Redis::connection('publisher');
    }
    
    /**
     * Store channel id to Redis db
     * 
     * @param int $chatChannelId
     */
    public function storeChatKey($chatChannelId)
    {
        try {
            $result = ChatChannel::where('id', $chatChannelId)->with('group')->first();
            self::$organizationId = $result->group->organization_id ?? '';
            //This function expect organization id every time
            $time = $this->getExpireTime();
            //false means no permission for timeout
            if (($time) && ($time!='false')) {
                self::$redis->set(config('chat.chat_timeout_key_prefix') . $chatChannelId, self::$organizationId, 'EX', $time);
            }
        } catch (\Exception $exception) {
            log_exception($exception);
        }
    }
   
    /**
     * Function for update expire time chat key 
     * This function is calling from all palces that customer responds 
     * 
     * @param string $key
     * @param int $organizationId
     */
    public function updateChatKeyExipreTime($chatChannelId)
    {
        try {
            $organizationId = self::$redis->get(config('chat.chat_timeout_key_prefix') . $chatChannelId);
            if ($organizationId) {
                self::$organizationId = $organizationId;
                //This function expect organization id every time
                $time = $this->getExpireTime();
                if (($time) && ($time!='false')) {
                    self::$redis->set(config('chat.chat_timeout_key_prefix') . $chatChannelId, self::$organizationId, 'EX', $time);
                } else {
                    //Handle for timeout permission disabled but some chats are active
                    self::$redis->set(config('chat.chat_timeout_key_prefix') . $chatChannelId, self::$organizationId);
                }
            }
        } catch (\Exception $exception) {
            log_exception($exception);
        }
    }
    
    /**
     * 
     * @param type $chatChannelId
     */
    public function destroyChatKey($chatChannelId)
    {
        self::$redis->del(config('chat.chat_timeout_key_prefix') . $chatChannelId);
    }
    
    /**
     * 
     * @return type
     * @todo Save expire time onn REDISwhile settings change from permission
     */
    private function getExpireTime()
    {
        try {
            $expireTime = (self::$organizationId!='') ? Redis::get(config('chat.org_chat_timeout_key_prefix').self::$organizationId) : config('chat.chat_default_expire_time')*60;
            //Yes no expire time on redis, get from db and save to redis
            if(empty($expireTime)) {
                $expireTime = (new OrganizationRolePermission)->getOrganizationChatTimeoutValue(self::$organizationId);
                $this->setOrganizationChatExpireTime(self::$organizationId, $expireTime);
            }
            return $expireTime;
        } catch (\Exception $exception) {
            log_exception($exception);
        }
    }
    
    /**
     * Function for set organization chat expire time 
     * 
     * @param type $organizationId
     * @param type $expireTime
     */
    public function setOrganizationChatExpireTime($organizationId, $expireTime)
    {
       Redis::set(config('chat.org_chat_timeout_key_prefix').$organizationId, $expireTime); 
    }


    /**
     * Function for chat close by Expire
     * 
     * @param int $chatChannelId
     */
    public function closeChat($chatChannelId)
    {
        try {
            //Select chat details if chat is active 
            $chatChanelData = ChatChannel::where('id', $chatChannelId)->activeSubscribers()->first();
            Log::debug("ExpireChatRepository::closeChat ==> chat " . $chatChannelId . ' is being closed');
            if($chatChanelData) {
                if ($chatChanelData->close(ChatChannel::CHANNEL_STATUS_TERMINATED_BY_VISITOR , true)) {
                    info("ExpireChatRepository::closeChat ==> chat " . $chatChanelData->id . ' is closed');
                    broadcast(new InformVisitorChannel([
                        'event' => 'chat_timeout',
                        'channel_name' => $chatChanelData->channel_name,
                    ]))->toOthers();
                } else {
                    Log::debug("ExpireChatRepository::closeChat ==> chat " . $chatChannelId . ' could not be closed');
                }
            } else {
                Log::debug("ExpireChatRepository::closeChat ==> chat " . $chatChannelId . ' could not be closed bcs empty set');
            }
        } catch (\Exception $exception) {
            log_exception($exception);
        }
    }
}