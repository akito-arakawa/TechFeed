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
        $schedule->command('articles:fetch qiita:new')->daily();
        $schedule->command('articles:fetch qiita:popular')->weekly();
        $schedule->command('articles:fetch qiita:popular')->weekly();
        $schedule->command('articles:fetch qiita:tag:react')->daily();
        $schedule->command('articles:fetch qiita:tag:laravel')->daily();
        $schedule->command('articles:fetch qiita:tag:javascrpit')->daily();
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
