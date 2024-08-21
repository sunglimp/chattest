<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class OrganizationValidityMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $subject;
    
    public $body;
    
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $body)
    {
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $date = date('d/m/Y',strtotime("today"));
        $mailData = $this->subject($this->subject.'|'.$date)
        ->view('emails.organization_validity_report')
        ->with(['body'=> $this->body,'date'=>$date]);
        
        return $mailData;
    }
}
