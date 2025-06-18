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
use Illuminate\Support\Facades\App; // <-- Make sure this is imported

class SendSingleReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $appointment;

    public function __construct(\stdClass $appointment)
    {
        $this->appointment = $appointment;
    }

    public function handle(WhatsappService $whatsapp): void
    {
        // Determine which template to use based on appointment status
        if ($this->appointment->app_status == 1) { // Confirmed
            $messageTemplate = config('reminders.template_confirmed');
        } elseif ($this->appointment->app_status == 0) { // Unconfirmed
            $messageTemplate = config('reminders.template_unconfirmed');
        } else {
            // It's good practice to handle unexpected statuses
            Log::warning("Tried to send reminder for an appointment with an invalid status.", [
                'appointment_id' => $this->appointment->appointment_id,
                'status' => $this->appointment->app_status
            ]);
            return;
        }

        // Prepare the placeholder values
        $patientName = $this->appointment->full_name;
        $appointmentTime = Carbon::parse($this->appointment->appointment_time)->format('g:i A');
        $doctorName = $this->appointment->doctor_name ?? 'العيادة'; // Default to العيادة if no doctor name

        // --- THIS IS THE CRITICAL PART FOR ARABIC DATES ---
        $originalLocale = App::getLocale();
        App::setLocale('ar'); // Temporarily set locale to Arabic
        // Format the date using translatedFormat to get Arabic day/month names
        $appointmentDate = Carbon::parse($this->appointment->appointment_date)->translatedFormat('l، j F Y');
        App::setLocale($originalLocale); // Revert to original locale
        // --- END OF CRITICAL PART ---

        // Replace placeholders with their values
        $message = str_replace(
            ['{patient_name}', '{appointment_time}', '{doctor_name}', '{appointment_date}'],
            [$patientName, $appointmentTime, $doctorName, $appointmentDate],
            $messageTemplate
        );

        // Send the message via your WhatsApp service
        $success = $whatsapp->sendMessage($this->appointment->mobile, $message);
        
        if ($success) {
            // Log that the reminder was sent
            SentReminder::create([
                'appointment_id' => $this->appointment->appointment_id,
                'sent_at' => now(),
            ]);
            Log::info("Reminder successfully dispatched to {$this->appointment->mobile} for appointment #{$this->appointment->appointment_id}");
        } else {
            Log::warning("Failed to dispatch reminder via WhatsApp API for appointment #{$this->appointment->appointment_id}");
        }
    }
}