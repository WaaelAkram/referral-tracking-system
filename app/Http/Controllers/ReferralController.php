<?php

namespace App\Http\Controllers;

class ReferralController extends Controller
{
    /**
     * Show the main application dashboard with the referral tools.
     */
    public function home()
    {
        // --- THIS IS THE FIX ---
        // Instead of trying to load a deleted view like 'referral.home',
        // we now correctly load the main 'dashboard' view.
        // We pass an empty `info` variable so the view doesn't error on first load.
        $info = null;
        return view('dashboard', compact('info'));
    }
}