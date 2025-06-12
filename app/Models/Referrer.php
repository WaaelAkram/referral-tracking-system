<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referrer extends Model
{
    use HasFactory;

    /**
     * The attributes that are not mass assignable.
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get all of the referrals for the Referrer.
     */
    public function referrals(): HasMany
    {
        // We specify the keys because they don't follow Laravel's convention
        return $this->hasMany(Referral::class, 'referrer_patient_id', 'referrer_patient_id');
    }
}