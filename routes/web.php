<?php

use App\Http\Controllers\Admin\ReferralDashboardController;
use App\Http\Controllers\ReferralEligibilityController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReferralCodeGeneratorController;

// Admin dashboard route
Route::prefix('admin')->group(function () {
    Route::get('/referrals', [ReferralDashboardController::class, 'index'])->name('admin.referrals');
});

// Home route (could be general referral page)
Route::get('/', [ReferralController::class, 'home'])->name('referral.home');

// Show the eligibility form
Route::get('/referral/eligibility', [ReferralEligibilityController::class, 'showForm'])->name('referral.eligibility.form');

// Handle form submission for both checking and adding
Route::post('/referral/eligibility/check', [ReferralEligibilityController::class, 'check'])->name('referral.eligibility.check');


//Route::post('/referral/generate-code', [ReferralCodeGeneratorController::class, 'generateCode'])->name('referral.generate_code');

// Generate referral code for a patient

Route::post('/referral/generate-code', [ReferralCodeGeneratorController::class, 'generateReferralCode'])->name('referral.generate_code');
