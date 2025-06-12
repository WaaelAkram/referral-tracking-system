<?php

namespace App\Http\Controllers;

use App\Services\ReferralService;
use Illuminate\Http\Request;

class ReferralInfoController extends Controller
{
    public function __construct(private ReferralService $referralService)
    {
    }

    public function referralInfoSearch(Request $request)
    {
        $request->validate(['search_term' => 'required|string']);

        try {
            $info = $this->referralService->getReferrerInfo($request->input('search_term'));
            return view('referral.home', compact('info'));
        } catch (\Exception $e) {
            return back()->withErrors(['search_term' => $e->getMessage()])->withInput();
        }
    }
}