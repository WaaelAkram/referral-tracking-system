

<?php

return [
    // How many minutes before the appointment to send the reminder.
    'window_minutes' => 60,

    // Message template for CONFIRMED (status 1) appointments.
    'template_confirmed' => "Hello {patient_name},\n\nThis is a friendly reminder of your appointment with {doctor_name} on {appointment_date} at {appointment_time}.\n\nWe look forward to seeing you!",

    // Message template for UNCONFIRMED (status 0) appointments.
    'template_unconfirmed' => "Hello {patient_name},\n\nYour appointment with {doctor_name} is scheduled for {appointment_date} at {appointment_time}. Please reply or call us to confirm.\n\nThank you.",
];