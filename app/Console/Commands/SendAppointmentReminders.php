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
    // ... (signature and description are the same) ...

    public function handle(ClinicPatientGateway $gateway): int
    {
        // This part is the same
        $reminderWindow = config('reminders.window_minutes', 60);
        $now = now();
        $startTime = $now->copy()->addMinutes($reminderWindow)->format('H:i:s');
        $endTime = $now->copy()->addMinutes($reminderWindow + 15)->format('H:i:s');

        // This now fetches both confirmed and unconfirmed appointments
        $appointments = $gateway->getAppointmentsInWindow($startTime, $endTime);

        if ($appointments->isEmpty()) {
            $this->info('No appointments found in the upcoming window.');
            return self::SUCCESS;
        }

        // This part is the same
        $sentIds = \App\Models\SentReminder::whereDate('sent_at', $now->toDateString())
            ->pluck('appointment_id')
            ->all();

        $dispatchedCount = 0;
        foreach ($appointments as $appointment) {
            if (!in_array($appointment->appointment_id, $sentIds)) {
                // *** UPDATED ***
                // We only need to pass the appointment object now.
                // The job is smart enough to find the right template.
                SendSingleReminder::dispatch($appointment);
                $dispatchedCount++;
            }
        }

        // This part is the same
        $logMessage = "Found {$appointments->count()} appointments in window. Dispatched {$dispatchedCount} new reminder jobs.";
        $this->info($logMessage);
        Log::info($logMessage);

        return self::SUCCESS;
    }
}