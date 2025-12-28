<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'transaction_id',
        'type',
        'session_id',
        'product_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Get the transaction that this item belongs to.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /**
     * Get the session for this item.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(SessionBilliard::class, 'session_id');
    }

    /**
     * Get the product for this item (F&B).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }
}
