<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feedback Request Timings
    |--------------------------------------------------------------------------
    */
    // Start looking for appointments that ended this many hours ago.
    'delay_hours_start' => 1,
    // Stop looking for appointments that ended this many hours ago.
    'delay_hours_end' => 2,

    /*
    |--------------------------------------------------------------------------
    | Feedback Request Template
    |--------------------------------------------------------------------------
    */
    'template' => "مرحبا {patient_name}، نتمنى ان زيارتكم لدى {doctor_name} كانت ممتازة. يهمنا معرفة رأيكم وملاحظاتكم. يرجى التقييم عبر الرابط: {feedback_link}",
    
    /*
    |--------------------------------------------------------------------------
    | Feedback URL
    |--------------------------------------------------------------------------
    |
    | The link that will be inserted into the {feedback_link} placeholder.
    |
    */
    'feedback_url' => 'https://g.page/r/CbnDhQAZmx01EAE/review',
];