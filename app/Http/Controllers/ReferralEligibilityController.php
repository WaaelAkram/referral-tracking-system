<?php

namespace App\Http\Controllers;

use App\Services\ReferralService;
use Illuminate\Http\Request;

class ReferralEligibilityController extends Controller
{
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
                return back()->with('success', '✅ Referral successfully added!')->withInput();
            }
            
            $this->referralService->checkEligibility($data['referral_code'], $data['mobile']);
            return back()->with('success', '✅ Referral is valid and eligible for rewards.')->withInput();
        } catch (\Exception $e) {
            // Return specific errors for the correct field if possible
            if (str_contains(strtolower($e->getMessage()), 'referral code')) {
                return back()->withErrors(['referral_code' => $e->getMessage()])->withInput();
            }
            return back()->withErrors(['mobile' => $e->getMessage()])->withInput();
        }
    }
}