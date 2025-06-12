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
        Schema::table('referrers', function (Blueprint $table) {
            // This adds only the 'updated_at' column.
            // We make it nullable() to prevent errors on existing rows.
            // We place it after 'created_at' for standard table structure.
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('referrers', function (Blueprint $table) {
            // This will correctly remove only the 'updated_at' column if you roll back.
            $table->dropColumn('updated_at');
        });
    }
};
