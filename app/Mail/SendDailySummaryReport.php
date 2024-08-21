<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDailySummaryReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $data;
    public function __construct($data)
    {
        $this->data=$data;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $reportdata= $this->data;
        $date=date('d/m/Y',strtotime("yesterday"));
        return $this->subject(ucfirst(config('constants.DAILY_SUMMARY_REPORT_SUBJECT')).' | '. $date)
        ->markdown('emails.daily_summary_report',compact('reportdata','date'));
    }
}
