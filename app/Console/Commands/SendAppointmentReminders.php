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

    // 1. Find the largest reminder window to define our database query scope.
    if (empty($reminderWindows)) {
        $this->error('Reminder windows are not configured correctly in config/reminders.php.');
        return self::FAILURE;
    }
    $maxWindowMinutes = max($reminderWindows);
    $this->info("Max reminder window is {$maxWindowMinutes} minutes.");

    // 2. Fetch all potentially eligible appointments in the largest possible window.
    $startTime = $now->copy()->format('H:i:s');
    $endTime = $now->copy()->addMinutes($maxWindowMinutes)->format('H:i:s');
    $appointmentsInMaxWindow = $gateway->getAppointmentsInWindow($startTime, $endTime);

    if ($appointmentsInMaxWindow->isEmpty()) {
        $this->info("No appointments found in the upcoming {$maxWindowMinutes} minute window.");
        return self::SUCCESS;
    }

    // 3. Filter out appointments we've already sent reminders for.
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
    // 4. Iterate through only the unsent appointments and check their specific window.
    foreach ($unsentAppointments as $appointment) {
        $status = $appointment->app_status;
        
        // Get the specific reminder window for this appointment's status.
        // If a status from the DB doesn't exist in our config, we skip it.
        if (!isset($reminderWindows[$status])) {
            continue;
        }
        $specificWindow = $reminderWindows[$status];

        // Check if the appointment time is within its specific window.
        $appointmentTime = Carbon::parse($appointment->appointment_time);
        
        // The check is from NOW up to the specific window limit.
        if ($appointmentTime->isBetween($now, $now->copy()->addMinutes($specificWindow))) {
            // This appointment is within its allowed time frame. Dispatch the job.
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