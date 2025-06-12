<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReferralEligibilityController;
use App\Http\Controllers\ReferralCodeGeneratorController;
use App\Http\Controllers\ReferralInfoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here we define all of the routes for the application.
|
*/

// The landing page for guests will now redirect to the login page.
Route::get('/', function () {
    return redirect()->route('login');
});

// The main authenticated dashboard and profile routes.
Route::middleware('auth')->group(function () {
    // The main dashboard will be served by the ReferralController's home method.
    Route::get('/dashboard', [ReferralController::class, 'home'])->name('dashboard');
    
    // --- CORRECTED REFERRAL TOOL ROUTES ---
    Route::post('/referral/eligibility/check', [ReferralEligibilityController::class, 'check'])->name('referral.eligibility.check');
    Route::post('/referral/generate-code', [ReferralCodeGeneratorController::class, 'generateReferralCode'])->name('referral.generate_code');
    Route::post('/referral-info', [ReferralInfoController::class, 'referralInfoSearch'])->name('referral.info.search');
    // --- END OF CORRECTION ---

    // Profile routes that were installed by Breeze.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// This file contains all the routes for login, logout, registration, etc.
require __DIR__.'/auth.php';