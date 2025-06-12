<?php

namespace App\Http\Controllers;

use App\Models\Referral; // Use the Eloquent model

class ReferralController extends Controller
{
    /**
     * Show the main application dashboard.
     * This view contains all the tools and the list of all referrals.
     */
    public function home()
    {
        // Fetch all referrals, paginated, to display on the dashboard.
        $referrals = Referral::with('reward')
            ->orderBy('referral_date', 'desc')
            ->paginate(10); // Changed to 10 for better display on one page

        // The 'info' variable for the search result is passed from a different controller,
        // so we don't need to define it here. The view will handle if it's set or not.
        return view('referral.home', compact('referrals'));
    }
}