<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReferralCodeGeneratorController extends Controller
{
    public function generateReferralCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $request->input('phone');

        $patient = DB::connection('mysql_referral_test')
            ->table('patient')
            ->where('mobile', $phone)
            ->first();

        if (!$patient) {
            return back()->withErrors(['phone' => 'Patient not found'])->withInput();
        }

        $referralCode = 'REF' . $patient->id;

        $exists = DB::table('referrers')
            ->where('referrer_patient_id', $patient->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['phone' => 'Referral code already exists for this patient'])->withInput();
        }

        DB::table('referrers')->insert([
            'referrer_patient_id' => $patient->id,
            'referral_code' => $referralCode,
            'referrer_phone' => $phone,
            'created_at' => Carbon::now(),
    
        ]);

        return back()->with([
            'generated_patient' => $patient,
            'generated_code' => $referralCode,
            'success_message' => 'Referral code generated and saved successfully.',
        ])->withInput();
    }
}
