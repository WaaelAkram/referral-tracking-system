<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\ReferralEligibilityController;
use App\Http\Controllers\ReferralCodeGeneratorController;
use App\Http\Controllers\ReferralInfoController;
use App\Http\Controllers\ReferralProcessController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Publicly accessible route for guests.
// It redirects to the login page.
Route::get('/', function () {
    return redirect()->route('login');
});

// All routes within this group require the user to be authenticated.
// This is where your main application functionality lives.


Route::get('/dashboard', [ReferralController::class, 'home'])->middleware('auth')->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [ReferralController::class, 'home'])->name('dashboard');

    // Referral Management Tools
    Route::post('/referral/eligibility/check', [ReferralEligibilityController::class, 'check'])->name('referral.eligibility.check');
    Route::post('/referral/generate-code', [ReferralCodeGeneratorController::class, 'generateReferralCode'])->name('referral.generate_code');
    Route::post('/referral-info', [ReferralInfoController::class, 'referralInfoSearch'])->name('referral.info.search');
    Route::post('/referral/process-rewards', ReferralProcessController::class)->name('referral.process_rewards');

    // User Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// This file contains all the routes for login, registration, password management, etc.
// It has its own internal middleware to handle guest/auth access correctly.
require __DIR__.'/auth.php';