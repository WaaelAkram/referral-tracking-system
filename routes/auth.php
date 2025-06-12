<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
| This file contains all the routes for handling user authentication.
| Email-based features like "Forgot Password" and "Email Verification"
| have been disabled to support a username-only system.
|
*/

// Routes for guests (users who are NOT logged in)
Route::middleware('guest')->group(function () {
    // Registration routes
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Login routes
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // ALL 'forgot-password' and 'reset-password' routes have been removed.
});

// Routes for authenticated users (users who ARE logged in)
Route::middleware('auth')->group(function () {
    // Confirm password feature (useful for sensitive actions)
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    // Update password feature (for the profile page)
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Logout route
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // ALL 'verify-email' routes have been removed.
});