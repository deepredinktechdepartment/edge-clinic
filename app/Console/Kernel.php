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
        // Run doctors sync once per day at 2 AM
        $schedule->command('doctors:sync')->dailyAt('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Load all commands in the Commands folder
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
