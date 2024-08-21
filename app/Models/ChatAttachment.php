<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAttachment extends Model
{
    public $timestamps = false;
    
    protected $guarded = [];
    
    /**
     * 
     * @param unknown $file
     * @param unknown $filePath
     * @param unknown $chatMessage
     */
    public static function saveData($file, $filePath, $chatMessage)
    {
        try {
            $message = json_decode($chatMessage->message);
            $hashName = ($message->hash_name) ?? '';
            $attachments = array(
                'hash_name' => $hashName,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize()* config('config.FILE_CONVERSION.FACTOR'),
                'path' => $filePath,
                'chat_message_id' => $chatMessage->id
            );
            return self::create($attachments);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
    
    /**
     * Function to ge attachment data.
     * 
     * @param string $attachmentData
     * @throws \Exception
     */
    public static function getAttachmentData($attachmentData)
    {
        try {
            return self::where('hash_name', $attachmentData)->select('original_name', 'path')->first();
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
}
