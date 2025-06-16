<?php
// app/Console/Kernel.php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        

        // This command will check for appointments every 15 minutes.
        $schedule->command('reminders:send')->everyFifteenMinutes();

       
       // $schedule->command('referrals:process')->daily();
    }

    // ...
}