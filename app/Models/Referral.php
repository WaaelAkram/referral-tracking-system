<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Referral extends Model
{
    use HasFactory;

    /**
     * The attributes that are not mass assignable.
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the referrer that owns this Referral.
     */
    public function referrer(): BelongsTo
    {
        // We specify the keys here because our table columns do not follow convention.
        return $this->belongsTo(Referrer::class, 'referrer_patient_id', 'referrer_patient_id');
    }

    /**
     * Get the reward associated with this Referral.
     */
    public function reward(): HasOne
    {
        // This relationship uses Laravel's default keys (referral_id), so we don't need to specify them.
        return $this->hasOne(Reward::class);
    }
}