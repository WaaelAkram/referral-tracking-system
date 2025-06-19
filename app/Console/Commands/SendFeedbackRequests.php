<?php

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
        $delayHoursStart = config('feedback.delay_hours_start', 2);
        $delayHoursEnd = config('feedback.delay_hours_end', 3);
        $now = Carbon::now();
        $startWindow = $now->copy()->subHours($delayHoursEnd)->format('H:i:s');
        $endWindow = $now->copy()->subHours($delayHoursStart)->format('H:i:s');
        $this->info("Looking for appointments that ended between {$startWindow} and {$endWindow}.");

        try {
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

        // --- THIS IS THE CORRECTED LOGIC ---

        // 1. Get IDs of all eligible appointments from this time window
        $appointmentIdsToCheck = $eligibleAppointments->pluck('appointment_id')->all();
        
        // 2. Query the database to find which of those IDs have already been processed
        $sentIds = SentFeedback::whereIn('appointment_id', $appointmentIdsToCheck)->pluck('appointment_id')->all();

        // 3. Create a new collection containing ONLY the appointments that haven't been sent yet
        $unsentAppointments = $eligibleAppointments->whereNotIn('appointment_id', $sentIds);
        
        // --- END OF CORRECTED LOGIC ---

        if ($unsentAppointments->isEmpty()) {
            $this->info("All eligible appointments in this window have already been sent a feedback request.");
            return self::SUCCESS;
        }

        $dispatchedCount = 0;
        // NOW, loop over the much smaller, pre-filtered collection
        foreach ($unsentAppointments as $appointment) {
            // No need for an if-check here, because we already filtered
            
            // --- ADDED YOUR MISSING CHECK FOR EMPTY MOBILE ---
            if (empty($appointment->mobile)) {
                Log::warning("Skipping feedback request for appointment #{$appointment->appointment_id} due to missing mobile number.");
                continue;
            }
            // --- END OF ADDED CHECK ---

            if ($dispatchedCount > 0) {
                $delaySeconds = rand(40, 120);
                $this->info("... waiting for {$delaySeconds} seconds before next message...");
                sleep($delaySeconds);
            }
            
            SendFeedbackRequest::dispatch($appointment);
            $dispatchedCount++;
        }

        // Improved log message for clarity
        $logMessage = "Feedback Check: Found {$eligibleAppointments->count()} eligible appointments. Found {$unsentAppointments->count()} new to process. Dispatched {$dispatchedCount} new feedback jobs.";
        $this->info($logMessage);
        Log::info($logMessage);

        return self::SUCCESS;
    }
}