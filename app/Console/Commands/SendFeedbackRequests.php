
<?php

namespace App\Console\Commands;

use App\Gateways\ClinicPatientGateway;
use App\Jobs\SendFeedbackRequest;
use App\Models\SentFeedbackRequest as SentFeedback;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // Make sure Carbon is imported

class SendFeedbackRequests extends Command
{
    protected $signature = 'feedback:send';
    protected $description = 'Finds appointments that recently finished and sends feedback requests.';

    public function handle(ClinicPatientGateway $gateway): int
    {
        $delayHoursStart = config('feedback.delay_hours_start', 2);
        $delayHoursEnd = config('feedback.delay_hours_end', 3);

        // Define the time window we're interested in
        $now = Carbon::now();
        $startWindow = $now->copy()->subHours($delayHoursEnd);   // 3 hours ago
        $endWindow = $now->copy()->subHours($delayHoursStart); // 2 hours ago

        $this->info("Processing appointments for today: " . $now->toDateString());
        $this->info("Looking for appointments that ended between {$startWindow->format('H:i')} and {$endWindow->format('H:i')}.");

        // 1. Fetch ALL of today's appointments from the database
        $todaysAppointments = $gateway->getAllAppointmentsForDate($now->toDateString());

        if ($todaysAppointments->isEmpty()) {
            $this->info('No appointments found for today.');
            return self::SUCCESS;
        }

        $eligibleAppointments = [];
        // 2. Loop through them in PHP to find the ones that are eligible
        foreach ($todaysAppointments as $appointment) {
            try {
                // Combine the date and time string and let Carbon parse it
                $endTimeString = $appointment->app_dt . ' ' . $appointment->to_tm;
                $appointmentEndTime = Carbon::parse($endTimeString);

                // Check if the appointment's end time is within our target window
                if ($appointmentEndTime->between($startWindow, $endWindow)) {
                    $eligibleAppointments[] = $appointment;
                }
            } catch (\Exception $e) {
                // Log if Carbon fails to parse a date/time, but don't stop the command
                Log::warning('Could not parse appointment time.', ['id' => $appointment->appointment_id, 'time_string' => $appointment->to_tm]);
                continue;
            }
        }

        if (empty($eligibleAppointments)) {
            $this->info('No appointments ended within the target window.');
            return self::SUCCESS;
        }

        // 3. Check for duplicates and dispatch jobs (this logic is the same)
        $appointmentIdsToCheck = collect($eligibleAppointments)->pluck('appointment_id')->all();
        $sentIds = SentFeedback::whereIn('appointment_id', $appointmentIdsToCheck)->pluck('appointment_id')->all();

        $dispatchedCount = 0;
        foreach ($eligibleAppointments as $appointment) {
            if (!in_array($appointment->appointment_id, $sentIds)) {
                SendFeedbackRequest::dispatch($appointment);
                $dispatchedCount++;
            }
        }

        $logMessage = "Feedback Check: Found " . count($eligibleAppointments) . " eligible appointments. Dispatched {$dispatchedCount} new feedback jobs.";
        $this->info($logMessage);
        Log::info($logMessage);

        return self::SUCCESS;
    }
}