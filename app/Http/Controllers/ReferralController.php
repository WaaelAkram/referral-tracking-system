<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    public function home()
    {
        return view('referral.home');
    }

    public function index()
    {
        $referrals = DB::table('referrals')
            ->leftJoin('referrers as r', 'referrals.referrer_patient_id', '=', 'r.referrer_patient_id')
            ->leftJoin('rewards as w', 'referrals.id', '=', 'w.referral_id')
            ->select('referrals.*', 'r.referral_code', 'w.reward_value', 'w.reward_type')
            ->orderBy('referrals.referral_date', 'desc')
            ->paginate(20);

        return view('admin.referrals.index', compact('referrals'));
    }
}
