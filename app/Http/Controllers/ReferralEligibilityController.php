<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralEligibilityController extends Controller
{
    public function showForm()
{
    return view('referral.home');
}
    public function check(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|string',
            'mobile' => 'required|string'
        ]);

        // Get referrer from referral_system
        $referrer = DB::table('referrers')
            ->where('referral_code', $request->referral_code)
            ->first();

        if (!$referrer) {
            return back()->withErrors(['referral_code' => 'Invalid referral code.'])->withInput();
        }

        // Get patient from referral_test database by mobile
        $externalPatient = DB::connection('mysql_referral_test')
            ->table('patient')
            ->where('mobile', $request->mobile)
            ->first();

        if (!$externalPatient) {
            return back()->withErrors(['mobile' => 'Patient not found in clinic records.'])->withInput();
        }

        // Prevent self-referrals
       if ($referrer->referrer_phone === $externalPatient->mobile) {
            return back()->withErrors(['mobile' => 'Referrer and referred cannot be the same person.'])->withInput();
        }

        // Check if patient is already referred
        $existingReferral = DB::table('referrals')
            ->where('referred_patient_id', $externalPatient->id)
            ->first();

        if ($existingReferral) {
            return back()->withErrors(['mobile' => 'This patient is already referred.'])->withInput();
        }

        // Optional: Prevent referred from being a referrer
        $isAlsoReferrer = DB::table('referrers')
            ->where('referrer_phone', $externalPatient->mobile)
            ->exists();

        if ($isAlsoReferrer) {
            return back()->withErrors(['mobile' => 'This patient is already a referrer.'])->withInput();
        }

        // Calculate total paid from invoice_h
        $totalPaid = DB::connection('mysql_referral_test')
            ->table('invoice_h')
            ->where('pt_id', $externalPatient->id)
            ->sum('net_amnt');

        $rewardThreshold = 3000;
        $rewardValue = 250;

        if ($request->input('action') === 'add') {
            // Insert referral record
            DB::table('referrals')->insert([
                'referrer_patient_id' => $referrer->id,
                'referred_patient_id' => $externalPatient->id,
                'referral_date' => now(),
                'status' => 'pending',
                'reward_issued' => 0,
                'total_paid' => $totalPaid,
                'referral_code_used' => $request->referral_code,
            ]);

            return view('referral.home', [
                'result' => '✅ Referral successfully added!',
                'referral_code' => $request->referral_code,
                'referrer' => $referrer,
                'referred_patient' => $externalPatient,
                'total_paid' => $totalPaid,
                'reward_threshold' => $rewardThreshold,
                'reward_value' => $rewardValue
            ]);
        }

        // Just preview
        return view('referral.home', [
            'result' => '✅ Referral is valid and eligible for rewards.',
            'referral_code' => $request->referral_code,
            'referrer' => $referrer,
            'referred_patient' => $externalPatient,
            'total_paid' => $totalPaid,
            'reward_threshold' => $rewardThreshold,
            'reward_value' => $rewardValue
        ]);
    }
}
