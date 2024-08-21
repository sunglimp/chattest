<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Swift_Attachment;
use Swift_SmtpTransport;
use Swift_Message;
use Swift_Mailer;

class SendChatEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

     public $mailData;

     public $agentName;

     public $file;

     public $config;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mailData, $agentName, $file, $config)
    {
        $this->mailData = $mailData;
        $this->agentName = $agentName;
        $this->file = $file;
        $this->config = $config;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
         $transport = new Swift_SmtpTransport($this->config['host'], $this->config['port']);
         $transport->setUsername($this->config['username']);
         $transport->setPassword($this->config['password']);
         $transport->setEncryption($this->config['encryption']);
         $mailer = new Swift_Mailer($transport);
         $cc = $this->mailData['recipient']['cc'] ?? '';
         $to = $this->mailData['recipient']['to'];
         $bcc = $this->mailData['recipient']['bcc'] ?? '';
         $subject = $this->mailData['subject'];
         $body = $this->mailData['body'];
         $from = $this->config['from_email'];
         $message   = (new Swift_Message($subject))
             ->setFrom($from)
             ->setTo($to)
             ->setCc($cc)
             ->setBcc($bcc)
             ->setBody(view('chat.email.email',['body'=>$body])->render(),'text/html');
             if (!empty($this->file)) {
            foreach($this->file as $attachments) {
                $message->attach(Swift_Attachment::fromPath('public/'.Storage::url($attachments['attachment_path']))->setFilename($attachments['attachment_file_name']));
            }
        }

         return $mailer->send($message);

        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
