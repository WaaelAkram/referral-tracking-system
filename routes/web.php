<?php

use App\Http\Controllers\Admin\ReferralDashboardController; // Add this at the top, after <?php

Route::prefix('admin')->group(function () {
    Route::get('/referrals', [ReferralDashboardController::class, 'index'])->name('admin.referrals');
});
