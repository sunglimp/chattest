<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\OfflineForm;

class SummarizeOfflineData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fromDate = $this->getBeginningOfflineDate();
        info("FROM DATE ".$fromDate);
        $toDate = Carbon::now(config('settings.default_timezone'))->format(config('settings.mysql_date_format'));
        $dates = get_dates_between_range($fromDate, $toDate);
        foreach($dates as $date)
        {
            $this->prepareCountOfflineQueries($date);
        }
    }
    
    private function getBeginningOfflineDate()
    {
        $row = OfflineForm::select('created_at')->orderby('created_at')->first();
        return $row->created_at ?? Carbon::now(config('settings.default_timezone'))->format(config('settings.mysql_date_format'));
    }
    
    private function prepareCountOfflineQueries($date)
    {
        info('Synchronize OfflineQueries Data to Summey Table :: ' . __METHOD__);
        info("Summerize for  ".$date);
        OfflineForm::getOfflineQueryDetails($date);
    }
}
