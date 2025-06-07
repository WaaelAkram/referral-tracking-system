<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReferralDashboardController extends Controller
{
    public function index()
    {
        $referrals = DB::table('referrals')
            ->leftJoin('referrers', 'referrals.referrer_patient_id', '=', 'referrers.referrer_patient_id')
            ->leftJoin('rewards', 'referrals.id', '=', 'rewards.referral_id')
            ->select(
                'referrals.*',
                'referrers.referral_code',
                'rewards.reward_value',
                'rewards.reward_type'
            )
            ->paginate(20);

        return view('admin.referrals.index', compact('referrals'));
    }
}
// 