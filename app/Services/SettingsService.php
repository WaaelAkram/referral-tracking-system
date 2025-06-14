<?php
// app/Services/SettingsService.php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class SettingsService
{
    /**
     * The path to the referrals configuration file.
     *
     * @var string
     */
    protected $configFilePath;

    public function __construct()
    {
        $this->configFilePath = config_path('referrals.php');
    }

    /**
     * Get all referral settings.
     *
     * @return array
     */
    public function getSettings(): array
    {
        return Config::get('referrals');
    }

    /**
     * Update the referral settings and write them to the config file.
     *
     * @param array $data The new settings data from the form.
     * @return void
     */
    public function updateSettings(array $data): void
    {
        // Get the current settings as a base
        $currentSettings = $this->getSettings();

        // Update with validated data
        $newSettings = [
            'reward_threshold' => $data['reward_threshold'] ?? $currentSettings['reward_threshold'],
            'reward_value' => $data['reward_value'] ?? $currentSettings['reward_value'],
            'reward_type' => $data['reward_type'] ?? $currentSettings['reward_type'],
        ];

        // Format the settings into a string that looks like a PHP file.
        $configContent = "<?php\n\nreturn " . var_export($newSettings, true) . ";\n";

        // Write the new content to the config/referrals.php file.
        File::put($this->configFilePath, $configContent);

        // It's crucial to clear the config cache after writing to the fi
        // so that the new values are loaded on the next request.
        if (app()->environment('production')) {
            \Artisan::call('config:cache');
        }
    }
}