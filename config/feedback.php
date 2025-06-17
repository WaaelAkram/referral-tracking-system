
<?php

return [
    // The delay (in hours) after an appointment ends to send the feedback request.
    'delay_hours' => 2,

    'template' => "Hello {patient_name}, we hope your visit with {doctor_name} today went well. We'd love to hear your feedback. Please share your thoughts here: {feedback_link}",

    'feedback_url' => 'https://g.page/r/CbnDhQAZmx01EAE/review',
];