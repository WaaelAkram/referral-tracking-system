<?php

namespace App\Http\Controllers;

use App\Services\ReferralService; // <-- Import the service
use Illuminate\Http\Request;

class ReferralEligibilityController extends Controller
{
    // Inject the service via the constructor
    public function __construct(private ReferralService $referralService)
    {
    }

        public function check(Request $request)
    {
        $data = $request->validate([
            'referral_code' => 'required|string|max:20',
            'mobile' => 'required|string|regex:/^\d{8,15}$/',
            'action' => 'required|string|in:check,add',
        ]);

        try {
            if ($data['action'] === 'add') {
                $this->referralService->createReferral($data['referral_code'], $data['mobile']);
                return redirect()->route('dashboard')->with('success', '✅ Referral successfully added!');
            }
            
            $this->referralService->checkEligibility($data['referral_code'], $data['mobile']);
            return redirect()->route('dashboard')->with('success', '✅ Referral is valid and eligible for rewards.');

        } catch (\Exception $e) {
            
            // --- THIS IS THE FIX ---
            // We now explicitly redirect to the 'dashboard' route on any error.
            
            $errorField = 'mobile'; // Default error field
            if (str_contains(strtolower($e->getMessage()), 'referral code')) {
                $errorField = 'referral_code';
            }

            return redirect()->route('dashboard')
                ->withErrors([$errorField => $e->getMessage()])
                ->withInput();
        }
    }
}