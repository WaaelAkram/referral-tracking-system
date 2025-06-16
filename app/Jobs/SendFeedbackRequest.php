<?php

namespace App\Jobs;

use App\Models\SentFeedbackRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFeedbackRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public \stdClass $appointment)
    {
    }

    public function handle(): void
    {
        $messageTemplate = config('feedback.template');
        $feedbackLink = config('feedback.feedback_url');

        $message = str_replace(
            ['{patient_name}', '{feedback_link}'],
            [$this->appointment->full_name, $feedbackLink],
            $messageTemplate
        );

        try {
            // Log the simulated action
            Log::channel('daily')->info(
                "WHATSAPP_SIMULATION: Feedback Request Sent.",
                [
                    'to' => $this->appointment->mobile,
                    'appointment_id' => $this->appointment->appointment_id,
                    'message_body' => $message,
                ]
            );

            // Record that this feedback request was "sent" to prevent duplicates
            SentFeedbackRequest::create([
                'appointment_id' => $this->appointment->appointment_id,
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::critical("FEEDBACK_SYSTEM_ERROR: Could not process job for appointment ID {$this->appointment->appointment_id}: " . $e->getMessage());
        }
    }
}