// database/migrations/2025_06_11_000000_create_referrers_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_patient_id')->unique();
            $table->string('referral_code')->unique();
            $table->string('referrer_phone');
            $table->timestamps(); // This creates both created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrers');
    }
};