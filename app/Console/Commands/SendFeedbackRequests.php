<?php
// app/Console/Commands/SendFeedbackRequests.php

namespace App\Console\Commands;

use App\Gateways\ClinicPatientGateway;
use App\Jobs\SendFeedbackRequest;
use App\Models\SentFeedbackRequest as SentFeedback;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendFeedbackRequests extends Command
{
    protected $signature = 'feedback:send';
    protected $description = 'Finds appointments that recently finished and sends feedback requests.';

    public function handle(ClinicPatientGateway $gateway): int
    {
        // --- THIS IS THE CORRECT, NEW LOGIC ---

        $delayHoursStart = config('feedback.delay_hours_start', 2);
        $delayHoursEnd = config('feedback.delay_hours_end', 3);

        $now = Carbon::now();
        // Calculate the time window. E.g., if it's 20:52, find appointments
        // between 17:52 (3 hours ago) and 18:52 (2 hours ago).
        $startWindow = $now->copy()->subHours($delayHoursEnd)->format('H:i:s');
        $endWindow = $now->copy()->subHours($delayHoursStart)->format('H:i:s');

        $this->info("Looking for appointments that ended between {$startWindow} and {$endWindow}.");

        // 1. Fetch ONLY the eligible appointments using the NEW gateway method
        try {
            // ** THE FIX IS HERE: We call the new method **
            $eligibleAppointments = $gateway->getAppointmentsFinishedInWindow($startWindow, $endWindow);

        } catch (\Exception $e) {
            $this->error("Failed to query the clinic database. Check the logs.");
            Log::error("Feedback command failed when calling getAppointmentsFinishedInWindow: " . $e->getMessage());
            return self::FAILURE;
        }

        if ($eligibleAppointments->isEmpty()) {
            $this->info('No appointments ended within the target window.');
            return self::SUCCESS;
        }

        // 2. Check for duplicates and dispatch jobs (this logic is correct and stays the same)
        $appointmentIdsToCheck = $eligibleAppointments->pluck('appointment_id')->all();
        $sentIds = SentFeedback::whereIn('appointment_id', $appointmentIdsToCheck)->pluck('appointment_id')->all();

        $dispatchedCount = 0;
        foreach ($eligibleAppointments as $appointment) {
            if (!in_array($appointment->appointment_id, $sentIds)) {
                SendFeedbackRequest::dispatch($appointment);
                $dispatchedCount++;
            }
        }

        $logMessage = "Feedback Check: Found {$eligibleAppointments->count()} eligible appointments. Dispatched {$dispatchedCount} new feedback jobs.";
        $this->info($logMessage);
        Log::info($logMessage);

        return self::SUCCESS;
    }
}