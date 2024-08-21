<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class EmailAttachment extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    
    /**
     * Add attachments for particular email.
     * 
     * @param string $filePath attachmemt file path
     * @param UploadedFile $file
     * @param string $actualFileName
     * @param EmailContent $emailContent
     * @throws \Exception
     */
    public static function addAttachments($fileData, $emailContent)
    {
        try {
            $fileData = array_map(function($arr) use($emailContent){
                return $arr + ['email_content_id' => $emailContent->id];
            }, $fileData);
                
                self::insert($fileData);
        } catch(\Exception $exception){
            throw $exception;
        }
    }
    
    /**
     * 
     * @throws Exception
     */
    public static function findAttachemnts($attachmentId)
    {
        try {
            return self::select('attachment_file_name', 'attachment_path')
                ->where('id', $attachmentId)
                ->first();
        } catch(\Exception $exception){
            throw $exception;
        }
    }
}
