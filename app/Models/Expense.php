<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'category',
        'payment_method',
        'expense_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    /**
     * User who created this expense
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Category labels for display
     */
    public static function getCategoryLabels(): array
    {
        return [
            'operasional' => 'Operasional',
            'gaji' => 'Gaji',
            'pembelian_stok' => 'Pembelian Stok',
            'lainnya' => 'Lainnya',
        ];
    }
}
