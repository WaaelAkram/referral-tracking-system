<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReferralEligibilityController;
use App\Http\Controllers\ReferralCodeGeneratorController;
use App\Http\Controllers\ReferralInfoController;
use App\Http\Controllers\ReferralProcessController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicDashboardController;
// ... (all your route definitions)
Route::get('/clinic-dashboard', [PublicDashboardController::class, 'index'])->name('public.dashboard');
Route::get('/clinic-dashboard/ar', [PublicDashboardController::class, 'index_ar'])->name('public.dashboard.ar');
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [ReferralController::class, 'home'])->name('dashboard');
    Route::post('/referral/eligibility/check', [ReferralEligibilityController::class, 'check'])->name('referral.eligibility.check');
    Route::post('/referral/generate-code', [ReferralCodeGeneratorController::class, 'generateReferralCode'])->name('referral.generate_code');
    Route::post('/referral-info', [ReferralInfoController::class, 'referralInfoSearch'])->name('referral.info.search');
    Route::post('/referral/process-rewards', ReferralProcessController::class)->name('referral.process_rewards');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// THIS LINE MUST BE PRESENT AND THE LAST LINE IN THE FILE
require __DIR__.'/auth.php';