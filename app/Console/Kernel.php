<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('telescope:prune')->dailyAt('00:05');
        $schedule->command('daily:summary')->dailyAt('00:05')->onOneServer();
        //$schedule->command('summary:create')->dailyAt('23:55');
        $time = config('config.SUMMARY_EXECUTE_TIME');
        $schedule->command('summary:create')->cron("*/$time * * * *")->onOneServer();
        //$schedule->command("chat:close")->everyMinute()->onOneServer();
        $schedule->command("history:check-last-activity")->everyMinute()->onOneServer();
        $schedule->command('update:unban-client')->dailyAt('23:50')->onOneServer();
        $schedule->command('organization:validity')->dailyAt('00:05')->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
