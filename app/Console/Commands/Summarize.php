<?php

namespace App\Console\Commands;
use \App\ {
    Jobs\PrepareAvgChat,
    Jobs\PrepareAvgFirstResponseTime,
    Jobs\PrepareAvgInteraction,
    Jobs\PrepareAvgOnlineDuration,
    Jobs\PrepareAvgResponseTime,
    Jobs\PrepareAvgSession,
    Jobs\PrepareCountChat,
    Jobs\PrepareCountChatEnteredChat,
    Jobs\PrepareCountChatMissed,
    Jobs\prepareCountOutSessionChatMissed,
    Jobs\PrepareCountChatResolved,
    Jobs\PrepareCountChatTerminatedByAgent,
    Jobs\PrepareCountChatTerminatedByVisitor,
    Jobs\PrepareCountChatTransferred,
    Jobs\PrepareCountEmailSent,
    Jobs\PrepareCountQueuedLeft,
    Jobs\PrepareCountQueuedVisitor,
    Jobs\PrepareCountInSessionTimeout,
    Jobs\PrepareOnlineDuration,
    Models\Summary,
    Models\LoginHistory,
    Jobs\PrepareAvgFirstResponseTimeToVisitor
};
use \Carbon\Carbon,
    \Illuminate\Console\Command;
use App\Models\ChatChannel;
use App\Models\OfflineForm;
use Illuminate\Support\Facades\Log;

class Summarize extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:create'
            . ' {--date= : If whole summary need to be created again}'
            . ' {--repair=false : If whole summary need to be created again}'
            . ' {--since-year=2019 : repair summary from; expected format `year(yyyy)`}'
            . ' {--since-month=01 : repair summary from; expected format `month(mm)`}'
            . ' {--since-day=01 : repair summary from; expected format `day(dd)`}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create summary of the chats';
    
    /**
     * This is the collection of agents needs to Summarize the data
     * 
     * @var type 
     */
    public $agents = [];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle($date = null)
    {
        if(is_null($date))
        {
            if($this->option('repair') == 'true')
            {
                if ($this->confirm('Do you really want to continue?')) {
                    $this->info('Summary is being prepared, need some time.');
                    $summarizeFromDate = $this->option('since-year') . '-'. $this->option('since-month') . '-'. $this->option('since-day');
                    $this->info('Starting from ' . $summarizeFromDate);
                    
                    $this->repair($this->option('since-year'), $this->option('since-month'), $this->option('since-day'));
                    return;
                    
                }
            }
        }
        
        if(!empty($this->option('date')))
        {
            $date = $this->option('date');
        }
        
        $now = $date ?? Carbon::now(config('settings.default_timezone'))->format(config('settings.mysql_date_format'));
        Log::debug("========================Start Summarize Initilization to Queue======================");
        $this->agents = LoginHistory::getUsersLogoutWithInTime();
        //@TODO now $this->agents will not includes admin type of users so remove extra conditions from all such prepareCounts queries
        Log::debug("******************Summarize With  agents : ". json_encode($this->agents) ."***************");
        Summary::where('summary_date', $now)->whereIn('agent_id', $this->agents)->delete();
        /**
         * Count Summary
         */
        $this->prepareCountChat($now);
        $this->prepareCountChatMissed($now);
        $this->prepareCountOutSessionChatMissed($now);
        $this->prepareCountEmailSent($now);
        $this->prepareCountChatTerminatedByAgent($now);
        $this->prepareCountChatTerminatedByVisitor($now);
        $this->prepareCountQueuedVisitor($now);
        $this->prepareCountChatEnteredChat($now);
        $this->prepareCountQueuedLeft($now);
        $this->PrepareCountInSessionTimeout($now);
        $this->prepareCountOnlineDuration($now);
        $this->prepareCountChatResolved($now);
        $this->prepareCountOfflineQueries($now);
        /**
         * Average Summary
         */
        $this->prepareAvgSession($now);
        $this->prepareCountChatTransferred($now);
        $this->prepareAvgChat($now);
        $this->prepareAvgInteraction($now);
        $this->prepareAvgOnlineDuration($now);
        $this->prepareAvgFirstResponseTime($now);
        $this->prepareAvgResponseTime($now);
        $this->prepareAvgFeedback($now);
        $this->prepareAvgFirstResponseTimeToVisitor($now);
        Log::debug("========================End Summarize Initilization======================");
    }

    private function repair($year, $month, $day)
    {
        $fromDate = $year . '-'. $month . '-' . $day;
        $toDate = Carbon::now(config('settings.default_timezone'))->format(config('settings.mysql_date_format'));
        $dates = get_dates_between_range($fromDate, $toDate);
        $bar = $this->output->createProgressBar(count($dates));
        $bar->start();
        foreach($dates as $date)
        {
            $this->handle($date);
            $bar->advance();
        }
        
        $bar->finish();
    }
   
            
    private function prepareCountChat($now)
    {
        info('Summary Create:: ' . __METHOD__);
        
        dispatch(new PrepareCountChat($now, $this->agents));
    }

    private function prepareCountChatTransferred($now)
    {
        info('Summary Create:: ' . __METHOD__);
        dispatch(new PrepareCountChatTransferred($now, $this->agents));
    }

    private function prepareCountChatResolved($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareCountChatResolved($now, $this->agents));
    }

    private function prepareCountChatTerminatedByAgent($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareCountChatTerminatedByAgent($now, $this->agents));
    }

    private function prepareCountChatTerminatedByVisitor($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareCountChatTerminatedByVisitor($now, $this->agents));
    }

    private function prepareCountChatMissed($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareCountChatMissed($now, $this->agents));
    }

    private function prepareCountOutSessionChatMissed($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new prepareCountOutSessionChatMissed($now));
    }

    private function prepareCountQueuedVisitor($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareCountQueuedVisitor($now));
    }

    private function prepareCountChatEnteredChat($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareCountChatEnteredChat($now, $this->agents));
    }

    private function prepareCountQueuedLeft($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareCountQueuedLeft($now));
    }

    private function PrepareCountInSessionTimeout($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareCountInSessionTimeout($now, $this->agents));
    }

    private function prepareCountEmailSent($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareCountEmailSent($now, $this->agents));
    }

    private function prepareAvgChat($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareAvgChat($now, $this->agents));
    }

    private function prepareAvgSession($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareAvgSession($now, $this->agents));
    }

    private function prepareAvgInteraction($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareAvgInteraction($now, $this->agents));
    }

    private function prepareAvgFirstResponseTime($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareAvgFirstResponseTime($now, $this->agents));
    }

    private function prepareAvgOnlineDuration($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareAvgOnlineDuration($now, $this->agents));
    }

    private function prepareCountOnlineDuration($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareOnlineDuration($now, $this->agents));
    }

    private function prepareAvgResponseTime($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareAvgResponseTime($now, $this->agents));
    }
    
    private function prepareAvgFeedback($now)
    {
        info('Summary Create:: ' . __METHOD__);
        
        ChatChannel::getFeedBack($now, $this->agents);
    }
    
    private function prepareAvgFirstResponseTimeToVisitor($now)
    {
        info('Summary Create:: ' . __METHOD__);

        dispatch(new PrepareAvgFirstResponseTimeToVisitor($now, $this->agents));
    }

    private function prepareCountOfflineQueries($now)
    {
        info('Summary Create:: ' . __METHOD__);
        
        OfflineForm::getOfflineQueryDetails($now);
    }

}
