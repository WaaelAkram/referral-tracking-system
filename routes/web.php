<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReferralEligibilityController;
use App\Http\Controllers\ReferralCodeGeneratorController;
use App\Http\Controllers\ReferralInfoController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| All application routes are now protected by the 'auth' middleware.
| Unauthenticated users will be redirected to the login screen.
|
*/

Route::middleware('auth')->group(function () {
    
    // The root URL is now the main dashboard.
    // We name it 'dashboard' so Breeze's post-login redirect works automatically.
    Route::get('/', [ReferralController::class, 'home'])->name('dashboard');

    // The form submission routes for the dashboard tools
    Route::post('/referral/eligibility/check', [ReferralEligibilityController::class, 'check'])->name('referral.eligibility.check');
    Route::post('/referral/generate-code', [ReferralCodeGeneratorController::class, 'generateReferralCode'])->name('referral.generate_code');
    Route::post('/referral-info', [ReferralInfoController::class, 'referralInfoSearch'])->name('referral.info.search');
    

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// --- Breeze Authentication Routes ---
require __DIR__.'/auth.php';