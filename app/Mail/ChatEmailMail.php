<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChatEmailMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $subject;
    
    public $body;
    
    public $sender;
    
    public $attachment;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $body, $from, $file)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->sender = $from;
        $this->attachment = $file;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mailData = $this->from([$this->sender])
        ->subject($this->subject)
        ->view('chat.email.email')
        ->with(['body'=> $this->body]);
        if (!empty($this->attachment)) {
            foreach($this->attachment as $attachments) {
                $mailData->attachFromStorage($attachments['attachment_path']);
            }
        }
        return $mailData;
    }
}
