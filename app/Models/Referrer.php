<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referrer extends Model
{
    use HasFactory;

    // DO NOT ADD 'public $timestamps = false;' here.
    // By default, timestamps are enabled, which is now what we want.

    protected $guarded = ['id'];

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_patient_id', 'referrer_patient_id');
    }
}