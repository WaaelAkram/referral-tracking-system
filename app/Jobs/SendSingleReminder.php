<?php
// app/Jobs/SendSingleReminder.php

namespace App\Jobs;

use App\Models\SentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendSingleReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param \stdClass $appointment
     */
    public function __construct(public \stdClass $appointment)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Determine which message template to use based on the status
        if ($this->appointment->app_status == 1) { // Confirmed
            $messageTemplate = config('reminders.template_confirmed');
        } elseif ($this->appointment->app_status == 0) { // Unconfirmed
            $messageTemplate = config('reminders.template_unconfirmed');
        } else {
            // If we somehow get a job for an invalid status, log it and stop.
            Log::warning("Tried to send reminder for an appointment with an invalid status.", [
                'appointment_id' => $this->appointment->appointment_id,
                'status' => $this->appointment->app_status
            ]);
            return; // Stop processing this job
        }

        // The rest of the logic is the same: personalize and "send" the message
        $patientName = $this->appointment->full_name;
        $appointmentTime = Carbon::parse($this->appointment->appointment_time)->format('g:i A');

        $message = str_replace(
            ['{patient_name}', '{appointment_time}'],
            [$patientName, $appointmentTime],
            $messageTemplate
        );

        try {
            // Log the simulated action
            Log::channel('daily')->info(
                "WHATSAPP_SIMULATION: Reminder Sent.",
                [
                    'to' => $this->appointment->mobile,
                    'appointment_id' => $this->appointment->appointment_id,
                    'status_sent' => $this->appointment->app_status == 1 ? 'Confirmed' : 'Unconfirmed',
                    'message_body' => $message,
                ]
            );

            // Record that this reminder was "sent"
            SentReminder::create([
                'appointment_id' => $this->appointment->appointment_id,
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::critical("REMINDER_SYSTEM_ERROR: Could not process job for appointment ID {$this->appointment->appointment_id}: " . $e->getMessage());
        }
    }
}