<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TableBilliard extends Model
{
    protected $table = 'tables_billiard';

    protected $fillable = [
        'table_number',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the sessions for this table.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(SessionBilliard::class, 'table_id');
    }

    /**
     * Get the active session for this table.
     */
    public function activeSession()
    {
        return $this->hasOne(SessionBilliard::class, 'table_id')
            ->where('status', 'playing')
            ->latest('start_time');
    }

    /**
     * Check if table is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Check if table is playing.
     */
    public function isPlaying(): bool
    {
        return $this->status === 'playing';
    }
}
