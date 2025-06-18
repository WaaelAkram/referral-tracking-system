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
    // ... (the logic to get the time window remains the same) ...
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

    $appointmentIdsToCheck = $eligibleAppointments->pluck('appointment_id')->all();
    $sentIds = SentFeedback::whereIn('appointment_id', $appointmentIdsToCheck)->pluck('appointment_id')->all();

    $dispatchedCount = 0;
    foreach ($eligibleAppointments as $appointment) {
        if (!in_array($appointment->appointment_id, $sentIds)) {

            // --- NEW: ADD DELAY BEFORE PROCESSING EACH APPOINTMENT ---
            if ($dispatchedCount > 0) { // No need to sleep before the very first one
                $delaySeconds = rand(40, 120);
                $this->info("... waiting for {$delaySeconds} seconds before next message...");
                sleep($delaySeconds);
            }
            // --- END OF NEW CODE ---

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