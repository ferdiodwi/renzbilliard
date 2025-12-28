<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rate extends Model
{
    protected $fillable = [
        'name',
        'price_per_hour',
    ];

    protected $casts = [
        'price_per_hour' => 'decimal:2',
    ];

    /**
     * Get the sessions using this rate.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(SessionBilliard::class, 'rate_id');
    }

    /**
     * Calculate price for given minutes.
     */
    public function calculatePrice(int $minutes): float
    {
        return round(($this->price_per_hour / 60) * $minutes, 2);
    }
}
