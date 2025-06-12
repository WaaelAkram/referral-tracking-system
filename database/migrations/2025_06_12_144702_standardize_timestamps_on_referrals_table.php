<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            // First, rename the existing column to match Laravel's convention.
            $table->renameColumn('referral_date', 'created_at');

            // Then, add the missing 'updated_at' column.
            // We make it nullable because existing rows won't have this value.
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     * This method contains the logic to undo the changes.
     */
    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            // First, remove the column we added.
            $table->dropColumn('updated_at');

            // Then, rename 'created_at' back to its original name.
            $table->renameColumn('created_at', 'referral_date');
        });
    }
};
