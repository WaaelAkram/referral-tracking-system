<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralEligibilityController extends Controller
{
    const REWARD_THRESHOLD = 3000;
    const REWARD_VALUE = 250;

    public function showForm()
    {
        return view('referral.home');
    }

    public function check(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string|max:20',
            'mobile' => 'required|string|regex:/^\d{8,15}$/',
        ]);

        $referrer = DB::table('referrers')
            ->where('referral_code', $request->referral_code)
            ->first();

        if (!$referrer) {
            return back()->withErrors(['referral_code' => 'Invalid referral code.'])->withInput();
        }

        $externalPatient = DB::connection('mysql_referral_test')
            ->table('patient')
            ->where('mobile', $request->mobile)
            ->first();

        if (!$externalPatient) {
            return back()->withErrors(['mobile' => 'Patient not found in clinic records.'])->withInput();
        }

        if ($referrer->referrer_phone === $externalPatient->mobile) {
            return back()->withErrors(['mobile' => 'Referrer and referred cannot be the same person.'])->withInput();
        }

        $existingReferral = DB::table('referrals')
            ->where('referred_patient_id', $externalPatient->id)
            ->first();

        if ($existingReferral) {
            return back()->withErrors(['mobile' => 'This patient is already referred.'])->withInput();
        }

        $isAlsoReferrer = DB::table('referrers')
            ->where('referrer_phone', $externalPatient->mobile)
            ->exists();

        if ($isAlsoReferrer) {
            return back()->withErrors(['mobile' => 'This patient is already a referrer.'])->withInput();
        }

        $totalPaid = DB::connection('mysql_referral_test')
            ->table('invoice_h')
            ->where('pt_id', $externalPatient->id)
            ->sum('net_amnt');

        if ($request->input('action') === 'add') {
            DB::transaction(function () use ($referrer, $externalPatient, $request, $totalPaid) {
                DB::table('referrals')->insert([
                    'referrer_patient_id' => $referrer->referrer_patient_id,
                    'referred_patient_id' => $externalPatient->id,
                    'referral_date' => now(),
                    'status' => 'pending',
                    'reward_issued' => 0,
                    'total_paid' => $totalPaid,
                    'referral_code_used' => $request->referral_code,
                ]);
            });

            return back()->with('success', '✅ Referral successfully added!')->withInput();
        }

        // Just checking eligibility
        return back()->with('success', '✅ Referral is valid and eligible for rewards.')->withInput();
    }
}
