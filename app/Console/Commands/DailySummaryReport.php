<?php

namespace App\Console\Commands;

use App\Models\ChatChannel;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\UserLogin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendDailySummaryReport;
use App\Models\Summary;

class DailySummaryReport extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To Send Daily summary report of organization   ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::alert("Daily Summary Resport Send to");

        $summaryData = Summary::getDailyReport();
        
        $mailRecievers = explode(',', config('config.SUMMARY_EMAIL_RECIEVERS'));
        
        Mail::to($mailRecievers)->send(new SendDailySummaryReport($summaryData));

        $this->info("Mail sent successfully");
    }
}
