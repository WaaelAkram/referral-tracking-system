<?php

namespace App\Http\Controllers;

// There are no other 'use' statements needed for this simple controller.

class ReferralController extends Controller
{
    /**
     * Show the main application dashboard with the referral tools.
     * This method no longer needs to pass any data to the view,
     * as the search results are handled by a different controller.
     */
    public function home()
    {
        return view('referral.home');
    }
}
