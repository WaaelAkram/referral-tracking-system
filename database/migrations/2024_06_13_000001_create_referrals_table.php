// database/migrations/2025_06_11_000001_create_referrals_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_patient_id');
            $table->unsignedBigInteger('referred_patient_id')->unique();
            $table->string('status'); // e.g., 'pending', 'completed'
            $table->boolean('reward_issued')->default(false);
            $table->decimal('total_paid', 8, 2)->default(0.00);
            $table->string('referral_code_used');
            $table->timestamps(); // Creates created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};