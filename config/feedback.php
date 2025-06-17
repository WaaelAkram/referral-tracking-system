<?php

return [
    // --- NEW, SIMPLER CONFIG ---
    // Start looking for appointments that ended this many hours ago.
    'delay_hours_start' => 2,
    // Stop looking for appointments that ended this many hours ago.
    'delay_hours_end' => 3,

    // --- REMAINDER OF FILE IS THE SAME ---
    'template' => "Hello {patient_name}, we hope your visit with {doctor_name} today went well. We'd love to hear your feedback. Please share your thoughts here: {feedback_link}",
    'feedback_url' => 'https://g.page/r/CbnDhQAZmx01EAE/review',
];