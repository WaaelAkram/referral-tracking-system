<?php
// app/Http/Controllers/ReferralProcessController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;

class ReferralProcessController extends Controller
{
    /**
     * Handle the incoming request to trigger the referral processing command.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request)
    {
        try {
            // Call the Artisan command. The 'call' method runs the command
            // and waits for it to finish.
            Artisan::call('referrals:process');

            // Get the output from the command to display to the user.
            $output = Artisan::output();

            // Redirect back to the dashboard with a success message and the output.
            return Redirect::route('dashboard')->with([
                'status' => 'success',
                'message' => 'Reward processing job completed successfully!',
                'command_output' => $output // We can display this if we want
            ]);

        } catch (\Exception $e) {
            // If the command fails for any reason, log the error and notify the user.
            \Log::error('Manual referral process trigger failed: ' . $e->getMessage());
            return Redirect::route('dashboard')
                ->withErrors(['process_rewards' => 'The reward processing job failed. Please contact support.']);
        }
    }
}