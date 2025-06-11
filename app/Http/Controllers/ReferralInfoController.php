<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferralInfoController extends Controller
{
    public function showForm()
    {
        return view('referral.home');
    }

    public function referralInfoSearch(Request $request)
    {
        $request->validate([
            'search_term' => 'required|string',
        ]);

        $input = $request->input('search_term');

        // Find referrer by referral code or phone number
        $referrer = DB::table('referrers')
            ->where('referral_code', $input)
            ->orWhere('referrer_phone', $input)
            ->first();

        if (!$referrer) {
            return back()->withErrors(['search_term' => 'No referrer found with this phone or referral code.'])->withInput();
        }

        // Get referrals made by this referrer
        $referrals = DB::table('referrals')
            ->where('referrer_patient_id', $referrer->referrer_patient_id)
            ->leftJoin('rewards', 'referrals.id', '=', 'rewards.referral_id')
            ->select('referrals.*', 'rewards.reward_value', 'rewards.reward_type')
            ->get();

        // Add patient data and total_paid per referral
        foreach ($referrals as $referral) {
            $patient = DB::connection('mysql_referral_test')
                ->table('patient')
                ->where('id', $referral->referred_patient_id)
                ->first();

            $referral->fname_a = $patient->fname_a ?? 'Unknown';
            $referral->lname_a = $patient->lname_a ?? '';
            $referral->mobile = $patient->mobile ?? '';

            $referral->total_paid = DB::connection('mysql_referral_test')
                ->table('invoice_h')
                ->where('pt_id', $referral->referred_patient_id)
                ->sum('net_amnt') ?? 0.00;
        }

        $totalReferrals = $referrals->count();
        $totalRewards = $referrals->sum(function ($item) {
            return (float) ($item->reward_value ?? 0);
        });

        $info = [
            'referrer_name' => $referrer->referrer_name ?? 'Unknown',
            'referral_code' => $referrer->referral_code,
            'total_referrals' => $totalReferrals,
            'total_rewards' => $totalRewards,
            'referred_patients' => $referrals,
        ];

        return view('referral.home', compact('info'));
    }
}
