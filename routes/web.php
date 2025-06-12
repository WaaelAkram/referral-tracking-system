<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReferralEligibilityController;
use App\Http\Controllers\ReferralCodeGeneratorController;
use App\Http\Controllers\ReferralInfoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| All application routes are defined here and are protected by the 'auth'
| middleware. Unauthenticated users will be redirected to the login screen.
|
*/

Route::middleware('auth')->group(function () {
    
    // The root URL is now the main dashboard, which shows everything.
    // We name it 'dashboard' so Breeze's post-login redirect works automatically.
    Route::get('/', [ReferralController::class, 'home'])->name('dashboard');

    // The form submission routes for the dashboard tools
    Route::post('/referral/eligibility/check', [ReferralEligibilityController::class, 'check'])->name('referral.eligibility.check');
    Route::post('/referral/generate-code', [ReferralCodeGeneratorController::class, 'generateReferralCode'])->name('referral.generate_code');
    Route::post('/referral-info', [ReferralInfoController::class, 'referralInfoSearch'])->name('referral.info.search');
    
});


// --- Breeze Authentication Routes ---
// This file contains all the routes for login, logout, registration, etc.
require __DIR__.'/auth.php';