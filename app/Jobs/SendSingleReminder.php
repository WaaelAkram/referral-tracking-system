<?php
// app/Jobs/SendSingleReminder.php

namespace App\Jobs;

// ... (use statements)
use Carbon\Carbon;
use Illuminate\Support\Facades\App; // <-- Add this use statement

class SendSingleReminder implements ShouldQueue
{
    // ... (constructor and other traits)

    public function handle(): void
    {
        // ... (logic to determine message template is the same)
        if ($this->appointment->app_status == 1) { // Confirmed
            $messageTemplate = config('reminders.template_confirmed');
        } elseif ($this->appointment->app_status == 0) { // Unconfirmed
            $messageTemplate = config('reminders.template_unconfirmed');
        } else {
            // ... (error logging)
            return;
        }

        // --- START OF CHANGES ---

        // Personalize the message variables
        $patientName = $this->appointment->full_name;
        $appointmentTime = Carbon::parse($this->appointment->appointment_time)->format('g:i A');
        $doctorName = $this->appointment->doctor_name ?? 'the doctor';

        // --- ARABIC DATE FORMATTING ---
        // 1. Get the original locale to reset it later
        $originalLocale = App::getLocale();

        // 2. Set the locale to Arabic and format the date
        App::setLocale('ar');
        $appointmentDate = Carbon::parse($this->appointment->appointment_date)->translatedFormat('l، j F Y'); // e.g., "الثلاثاء، 18 يونيو 2024"

        // 3. Reset the locale to its original state
        App::setLocale($originalLocale);
        // --- END OF ARABIC DATE FORMATTING ---

        // Replace all placeholders in the message template
        $message = str_replace(
            ['{patient_name}', '{appointment_time}', '{doctor_name}', '{appointment_date}'],
            [$patientName, $appointmentTime, $doctorName, $appointmentDate],
            $messageTemplate
        );

        try {
            // Log the simulated action
            Log::channel('daily')->info(
                "WHATSAPP_SIMULATION: Reminder Sent.",
                [
                    'to' => $this->appointment->mobile,
                    'appointment_id' => $this->appointment->appointment_id,
                    'doctor_name' => $doctorName,
                    'appointment_date_formatted' => $appointmentDate, // <-- Add formatted date to log
                    'status_sent' => $this->appointment->app_status == 1 ? 'Confirmed' : 'Unconfirmed',
                    'message_body' => $message,
                ]
            );

            // --- END OF CHANGES ---

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