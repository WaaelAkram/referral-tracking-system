// In config/referrals.php

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Referral Reward Settings
    |--------------------------------------------------------------------------
    |
    | These values control the business logic for when a referral qualifies
    | for a reward and how much that reward is worth.
    |
    */

    'reward_threshold' => env('REFERRAL_REWARD_THRESHOLD', 3000),

    'reward_value' => env('REFERRAL_REWARD_VALUE', 250),

];