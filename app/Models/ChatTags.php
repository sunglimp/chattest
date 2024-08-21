<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatTags extends Model
{
    protected $table = 'chat_tags';
    public $timestamps = false;
    
    protected $fillable = ['tag_name', 'tag_id', 'chat_channel_id'];
    
    
    public static function getChatTags($channelId)
    {
        return self::selectRaw('GROUP_CONCAT(tag_name) as tags')->where('chat_channel_id', $channelId)->first();
    }
}
