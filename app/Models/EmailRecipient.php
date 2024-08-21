<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailRecipient extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    
    /**
     * Function to save data in chat_email
     * @param array $emailData
     * @throws \Exception
     */
    public static function addRecipients($emailData, EmailContent $emailContent)
    {
        try {
            $insertionData = array();
            $recipients = $emailData['recipient'];
            foreach($recipients as $key =>$senders) {
                foreach ($senders as $send) {
                    $insertionData[] = array(
                        'email_address' => $send,
                        'recipient_type' => $key,
                        'email_content_id' => $emailContent->id
                    );
                }
            }
    
            EmailRecipient::insert($insertionData);
        } catch(\Exception $exception) {
            throw $exception;
        }
    }
}
