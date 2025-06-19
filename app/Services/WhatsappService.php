<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $apiUrl;

    public function __construct()
    {
        $host = config('whatsapp.api_host');
        $port = config('whatsapp.api_port');
        $this->apiUrl = "{$host}:{$port}";
    }

    /**
     * Formats a local phone number into the international E.164 format for WhatsApp.
     * Assumes Saudi Arabia country code (966).
     *
     * @param string $number
     * @return string
     */
    private function formatNumber(string $number): string
    {
        // Remove any non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // If number starts with '05', replace '0' with the country code '966'
        if (str_starts_with($number, '05')) {
            return '966' . substr($number, 1);
        }

        // If number already starts with '966' and is the correct length, it's fine
        if (str_starts_with($number, '966')) {
            return $number;
        }
        
        // Add any other rules you might need here. For now, we return the cleaned number.
        return $number;
    }

    /**
     * Sends a message via the WhatsApp API service.
     *
     * @param string $to The recipient's phone number.
     * @param string $message The message content.
     * @return bool True on success, false on failure.
     */
    public function sendMessage(string $to, string $message): bool
    {
        // --- THIS IS THE KEY CHANGE ---
        $formattedNumber = $this->formatNumber($to);
        // --- END OF CHANGE ---

        try {
            $response = Http::timeout(30)->post("{$this->apiUrl}/send-message", [
                'to' => $formattedNumber, // Use the formatted number
                'message' => $message,
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp message successfully sent to {$to} (formatted as {$formattedNumber}). Response: " . $response->body());
                return true;
            }

            Log::error("Failed to send WhatsApp message to {$to}. Status: {$response->status()}. Body: {$response->body()}");
            return false;

        } catch (\Exception $e) {
            Log::critical("Exception while trying to send WhatsApp message to {$to}: " . $e->getMessage());
            return false;
        }
    }
}