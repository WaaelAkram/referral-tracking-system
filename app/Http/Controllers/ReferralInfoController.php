<?php

namespace App\Http\Controllers;

use App\Services\ReferralService;
use Illuminate\Http\Request;

class ReferralInfoController extends Controller
{
    // Inject the service via the constructor
    public function __construct(private ReferralService $referralService)
    {
    }

    public function referralInfoSearch(Request $request)
    {
        $request->validate(['search_term' => 'required|string']);

        try {
            $info = $this->referralService->getReferrerInfo($request->input('search_term'));
            
            // On success, we show the dashboard view with the results.
            return view('dashboard', compact('info'));

        } catch (\Exception $e) {
            
            // --- THIS IS THE FIX ---
            // Instead of using back(), we explicitly redirect to the 'dashboard' route.
            // We still pass along the errors and the user's original input.
            return redirect()->route('dashboard')
                ->withErrors(['search_term' => $e->getMessage()])
                ->withInput();
            // --- END OF FIX ---
        }
    }
}