<?php

namespace App\Jobs;

use App\Models\SentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class SendSingleReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The appointment instance.
     * @var \stdClass
     */
    public $appointment;

    /**
     * Create a new job instance.
     * @param \stdClass $appointment
     */
    public function __construct(\stdClass $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->appointment->app_status == 1) { // Confirmed
            $messageTemplate = config('reminders.template_confirmed');
        } elseif ($this->appointment->app_status == 0) { // Unconfirmed
            $messageTemplate = config('reminders.template_unconfirmed');
        } else {
            Log::warning("Tried to send reminder for an appointment with an invalid status.", [
                'appointment_id' => $this->appointment->appointment_id,
                'status' => $this->appointment->app_status
            ]);
            return;
        }

        $patientName = $this->appointment->full_name;
        $appointmentTime = Carbon::parse($this->appointment->appointment_time)->format('g:i A');
        $doctorName = $this->appointment->doctor_name ?? 'the doctor';

        $originalLocale = App::getLocale();
        App::setLocale('ar');
        $appointmentDate = Carbon::parse($this->appointment->appointment_date)->translatedFormat('l، j F Y');
        App::setLocale($originalLocale);

        $message = str_replace(
            ['{patient_name}', '{appointment_time}', '{doctor_name}', '{appointment_date}'],
            [$patientName, $appointmentTime, $doctorName, $appointmentDate],
            $messageTemplate
        );

        try {
            Log::channel('daily')->info(
                "WHATSAPP_SIMULATION: Reminder Sent.",
                [
                    'to' => $this->appointment->mobile,
                    'appointment_id' => $this->appointment->appointment_id,
                    'doctor_name' => $doctorName,
                    'appointment_date_formatted' => $appointmentDate,
                    'status_sent' => $this->appointment->app_status == 1 ? 'Confirmed' : 'Unconfirmed',
                    'message_body' => $message,
                ]
            );

            SentReminder::create([
                'appointment_id' => $this->appointment->appointment_id,
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::critical("REMINDER_SYSTEM_ERROR: Could not process job for appointment ID {$this->appointment->appointment_id}: " . $e->getMessage());
        }
    }
}