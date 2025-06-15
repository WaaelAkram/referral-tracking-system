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
    public function handle(ClinicPatientGateway $gateway): int
    {
        $reminderWindowMinutes = config('reminders.window_minutes', 60);
        $now = now();

        // --- NEW, MORE ROBUST LOGIC ---
        // We look for all appointments from right now up to our reminder window limit.
        $startTime = $now->copy()->format('H:i:s');
        $endTime = $now->copy()->addMinutes($reminderWindowMinutes)->format('H:i:s');

        // Fetch all potentially eligible appointments in the entire window.
        $appointments = $gateway->getAppointmentsInWindow($startTime, $endTime);

        if ($appointments->isEmpty()) {
            $this->info("No appointments found in the upcoming $reminderWindowMinutes minute window.");
            return self::SUCCESS;
        }

        // Get the IDs of all appointments we found from the clinic DB.
        $appointmentIdsToCheck = $appointments->pluck('appointment_id')->all();

        // Ask our *local* database which of these we have already processed.
        $sentIds = SentReminder::whereIn('appointment_id', $appointmentIdsToCheck)
            ->pluck('appointment_id')
            ->all();

        $dispatchedCount = 0;
        foreach ($appointments as $appointment) {
            // The main condition: if the current appointment's ID is NOT in the list of sent IDs...
            if (!in_array($appointment->appointment_id, $sentIds)) {
                // ...then we need to send a reminder.
                SendSingleReminder::dispatch($appointment);
                $dispatchedCount++;
            }
        }

        $logMessage = "Checked {$appointments->count()} appointments. Dispatched {$dispatchedCount} new reminder jobs.";
        $this->info($logMessage);
        Log::info($logMessage);

        return self::SUCCESS;
    }
}