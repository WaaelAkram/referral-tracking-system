<?php

// config/referrals.php

return [
    /*
    |--------------------------------------------------------------------------
    | Referral Reward Settings
    |--------------------------------------------------------------------------
    | These values determine the conditions and value of a referral reward.
    | They can be overridden by your .env file.
    */

    'reward_threshold' => env('REFERRAL_REWARD_THRESHOLD', 500),
    'reward_value' => env('REFERRAL_REWARD_VALUE', 50),
    'reward_type' => env('REFERRAL_REWARD_TYPE', 'SAR Credit'),
];