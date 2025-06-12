<?php

namespace App\Http\Controllers;

class ReferralController extends Controller
{
    /**
     * Show the main application dashboard with the referral tools.
     */
    public function home()
    {
        // The dashboard view now contains all tools and lists.
        // We'll pass an empty `info` variable so the view doesn't error on first load.
        $info = null;
        return view('dashboard', compact('info'));
    }
}