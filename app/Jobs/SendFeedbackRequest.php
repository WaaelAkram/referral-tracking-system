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

    public $appointment;

    public function __construct(\stdClass $appointment)
    {
        $this->appointment = $appointment;
    }

    public function handle(): void
    {
        $messageTemplate = config('feedback.template');
        $feedbackLink = config('feedback.feedback_url');

        $patientName = $this->appointment->full_name;
        $doctorName = $this->appointment->doctor_name ?? 'the clinic';

        $message = str_replace(
            ['{patient_name}', '{doctor_name}', '{feedback_link}'],
            [$patientName, $doctorName, $feedbackLink],
            $messageTemplate
        );

        try {
            Log::channel('daily')->info(
                "WHATSAPP_SIMULATION: Feedback Request Sent.",
                [
                    'to' => $this->appointment->mobile,
                    'appointment_id' => $this->appointment->appointment_id,
                    'doctor_name' => $doctorName,
                    'message_body' => $message,
                ]
            );

            SentFeedbackRequest::create([
                'appointment_id' => $this->appointment->appointment_id,
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::critical("FEEDBACK_SYSTEM_ERROR: Could not process job for appointment ID {$this->appointment->appointment_id}: " . $e->getMessage());
        }
    }
}