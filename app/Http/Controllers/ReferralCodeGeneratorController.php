<?php

namespace App\Http\Controllers;

use App\Services\ReferralService;
use Illuminate\Http\Request;

class ReferralCodeGeneratorController extends Controller
{
    public function __construct(private ReferralService $referralService)
    {
    }

    public function generateReferralCode(Request $request)
    {
        $request->validate(['phone' => 'required|string']);

        try {
            $result = $this->referralService->generateCodeForPatient($request->input('phone'));
            
            return back()->with([
                'generated_patient' => $result['patient'],
                'generated_code' => $result['code'],
                'success_message' => 'Referral code generated and saved successfully.',
            ])->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['phone' => $e->getMessage()])->withInput();
        }
    }
}