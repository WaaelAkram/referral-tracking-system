<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the referral that the Reward belongs to.
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }
}