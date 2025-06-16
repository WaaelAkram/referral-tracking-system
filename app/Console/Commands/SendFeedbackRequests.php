<?php

namespace App\Console\Commands;

use App\Gateways\ClinicPatientGateway;
use App\Jobs\SendFeedbackRequest;
use App\Models\SentFeedbackRequest as SentFeedback; // Using an alias for clarity
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendFeedbackRequests extends Command
{
    protected $signature = 'feedback:send';
    protected $description = 'Finds appointments that recently finished and sends feedback requests.';

    public function handle(ClinicPatientGateway $gateway): int
    {
        $startHours = config('feedback.delay_hours_start');
        $endHours = config('feedback.delay_hours_end');

        // Define the time window in the past.
        // For a 2-3 hour delay, we look for appointments that ended
        // between 3 hours ago and 2 hours ago.
        $startBoundary = now()->subHours($endHours);
        $endBoundary = now()->subHours($startHours);

        $this->info("Checking for appointments that finished between {$startBoundary->format('Y-m-d H:i')} and {$endBoundary->format('Y-m-d H:i')}.");

        // Call our new, more accurate gateway method.
        $appointments = $gateway->getAppointmentsFinishedBetween($startBoundary, $endBoundary);

        if ($appointments->isEmpty()) {
            $this->info('No appointments finished in the target window.');
            return self::SUCCESS;
        }

        // The rest of the logic for preventing duplicates is the same.
        $appointmentIdsToCheck = $appointments->pluck('appointment_id')->all();
        $sentIds = SentFeedback::whereIn('appointment_id', $appointmentIdsToCheck)->pluck('appointment_id')->all();

        $dispatchedCount = 0;
        foreach ($appointments as $appointment) {
            if (!in_array($appointment->appointment_id, $sentIds)) {
                SendFeedbackRequest::dispatch($appointment);
                $dispatchedCount++;
            }
        }

        $logMessage = "Feedback Check: Found {$appointments->count()} finished appointments. Dispatched {$dispatchedCount} new feedback jobs.";
        $this->info($logMessage);
        Log::info($logMessage);

        return self::SUCCESS;
    }
}