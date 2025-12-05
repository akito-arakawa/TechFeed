<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('fetch:qitta popular')->weekly();
        $schedule->command('fetch:qitta tag react')->daily();
        $schedule->command('fetch:qitta tag laravel')->daily();
        $schedule->command('fetch:qitta tag javascrpit')->daily();
        $schedule->command('fetch:zenn new')->daily()->appendOutputTo(storage_path('logs/zenn.log'));
        $schedule->command('fetch:zenn popular weekly')->weekly()->appendOutputTo(storage_path('logs/zenn.log'));
        $schedule->command('fetch:zenn popular alltime')->monthly()->appendOutputTo(storage_path('logs/zenn.log'));
        $schedule->command('fetch:zenn tag react')->weekly()->appendOutputTo(storage_path('logs/zenn.log'));
        $schedule->command('fetch:zenn tag laravel')->weekly()->appendOutputTo(storage_path('logs/zenn.log'));
        $schedule->command('fetch:zenn tag javascrpit')->weekly()->appendOutputTo(storage_path('logs/zenn.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
