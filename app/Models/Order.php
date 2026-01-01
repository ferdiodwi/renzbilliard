<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_name',
        'session_id',
        'cashier_id',
        'subtotal',
        'tax',
        'total',
        'status',
        'payment_method',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the session associated with this order
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(SessionBilliard::class, 'session_id');
    }

    /**
     * Get the cashier who created this order
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Get the order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = static::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastOrder 
            ? (int) substr($lastOrder->order_number, -4) + 1 
            : 1;

        return sprintf('FNB-%s-%04d', $date, $sequence);
    }

    /**
     * Scope for pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed orders
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
