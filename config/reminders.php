

<?php

return [
    'windows' => [
        // For Confirmed (status 1) appointments, send reminder 60 minutes before.
        1 => env('REMINDER_WINDOW_CONFIRMED_MINUTES', 120),

        // For Unconfirmed (status 0) appointments, send reminder 120 minutes before.
        0 => env('REMINDER_WINDOW_UNCONFIRMED_MINUTES', 240),
    ],

    // Message template for CONFIRMED (status 1) appointments.
    'template_confirmed' => "مرحبا {patient_name},\n\nنود تذكيركم بالموعد لدى مركز داف لطب الاسنان عند{doctor_name} بتاريخ {appointment_date} الساعة {appointment_time}.\n\nشكرا لكم",

    // Message template for UNCONFIRMED (status 0) appointments.
    'template_unconfirmed' => "مرحبا {patient_name},\n\nموعدك لدى مركز داف لطب الاسنان عند {doctor_name} بتاريخ {appointment_date} الساعة {appointment_time}. يرجى تاكيد الموعد عبر هذه المحادثة او الاتصال.\n\nشكرا لكم.",
];