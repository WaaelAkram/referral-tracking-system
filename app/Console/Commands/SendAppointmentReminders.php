<?php
// app/Console/Commands/SendAppointmentReminders.php

namespace App\Console\Commands;

use App\Gateways\ClinicPatientGateway;
use App\Jobs\SendSingleReminder;
use App\Models\SentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Finds all upcoming appointments within the reminder window and sends notifications.';

    /**
     * The main logic for the command.
     */
    // In app/Console/Commands/SendAppointmentReminders.php

// ... (your use statements at the top)

public function handle(ClinicPatientGateway $gateway): int
{
    $reminderWindows = config('reminders.windows');
    $now = now();

    // ... (the logic to fetch appointments remains the same) ...
    $maxWindowMinutes = max($reminderWindows);
    $this->info("Max reminder window is {$maxWindowMinutes} minutes.");

    $startTime = $now->copy()->format('H:i:s');
    $endTime = $now->copy()->addMinutes($maxWindowMinutes)->format('H:i:s');
    $appointmentsInMaxWindow = $gateway->getAppointmentsInWindow($startTime, $endTime);

    if ($appointmentsInMaxWindow->isEmpty()) {
        $this->info("No appointments found in the upcoming {$maxWindowMinutes} minute window.");
        return self::SUCCESS;
    }

    $appointmentIdsToCheck = $appointmentsInMaxWindow->pluck('appointment_id')->all();
    $sentIds = SentReminder::whereIn('appointment_id', $appointmentIdsToCheck)
        ->pluck('appointment_id')
        ->all();
    
    $unsentAppointments = $appointmentsInMaxWindow->whereNotIn('appointment_id', $sentIds);

    if ($unsentAppointments->isEmpty()) {
        $this->info("All upcoming appointments have already been reminded.");
        return self::SUCCESS;
    }

    $dispatchedCount = 0;
    foreach ($unsentAppointments as $appointment) {
        
        // --- NEW: ADD DELAY BEFORE PROCESSING EACH APPOINTMENT ---
        if ($dispatchedCount > 0) { // No need to sleep before the very first one
            $delaySeconds = rand(40, 120);
            $this->info("... waiting for {$delaySeconds} seconds before next message...");
            sleep($delaySeconds);
        }
        // --- END OF NEW CODE ---

        $status = $appointment->app_status;
        
        if (!isset($reminderWindows[$status])) {
            continue;
        }
        $specificWindow = $reminderWindows[$status];

        $appointmentTime = Carbon::parse($appointment->appointment_time);
        
        if ($appointmentTime->isBetween($now, $now->copy()->addMinutes($specificWindow))) {
            SendSingleReminder::dispatch($appointment);
            $dispatchedCount++;
            $this->line(" -> Queued reminder for appointment #{$appointment->appointment_id} (Status: {$status}, Window: {$specificWindow} mins)");
        }
    }

    $logMessage = "Checked {$appointmentsInMaxWindow->count()} appointments. Dispatched {$dispatchedCount} new reminder jobs.";
    $this->info($logMessage);
    Log::info($logMessage);

    return self::SUCCESS;
}
}