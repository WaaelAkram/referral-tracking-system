<?php

namespace App\Http\Controllers;

use App\Services\ReferralService; // <-- Import the service
use Illuminate\Http\Request;

class ReferralCodeGeneratorController extends Controller
{
    // Inject the service via the constructor
    public function __construct(private ReferralService $referralService)
    {
    }

      public function generateReferralCode(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        try {
            $result = $this->referralService->generateCodeForPatient($request->input('phone'));
            
            // On success, we redirect to the dashboard with flash data
            return redirect()->route('dashboard')->with([
                'generated_patient' => $result['patient'],
                'generated_code' => $result['code'],
                'success_message' => 'Referral code generated and saved successfully.',
            ]);

        } catch (\Exception $e) {

            // --- THIS IS THE FIX ---
            // We now explicitly redirect to the 'dashboard' route on any error.
            return redirect()->route('dashboard')
                ->withErrors(['phone' => $e->getMessage()])
                ->withInput();
        }
    }
}