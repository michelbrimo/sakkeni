<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('preferences:fill-from-searches')->twiceDaily(1, 13);
        // commad: php artisan preferences:fill-from-searches

        // $schedule->command('metrics:update-property-popularity')->dailyAt('02:00');
        // commad: php artisan metrics:update-property-popularity

        // $schedule->command('recommendations:retrain-models')->dailyAt('03:00');
        // commad: php artisan recommendations:retrain-models



    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
