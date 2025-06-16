<?php
// app/Console/Kernel.php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // ... any other commands ...

        // This command will check for appointments every 15 minutes.
        $schedule->command('reminders:send')->everyFifteenMinutes();

        // NEW: This command will check for completed appointments every hour.
        $schedule->command('feedback:send')->everyFifteenMinutes();

        // Your existing referral processing job can also be scheduled here.
        // $schedule->command('referrals:process')->daily();
    }

    // ...
}