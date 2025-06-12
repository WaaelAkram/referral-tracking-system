<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Referral extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the referrer that owns the Referral.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class, 'referrer_patient_id', 'referrer_patient_id');
    }

    /**
     * Get the reward associated with the Referral.
     */
    public function reward(): HasOne
    {
        return $this->hasOne(Reward::class);
    }
}