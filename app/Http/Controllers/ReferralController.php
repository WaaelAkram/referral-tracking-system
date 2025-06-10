<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function home()
    {
        return view('referral.home');
    }

    // You can add other referral functions here later
}
