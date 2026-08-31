<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OrderRefundFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderRefund extends Model
{
    /** @use HasFactory<OrderRefundFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amount',
        'reason',
        'refunded_by',
        'restocked',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'restocked' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderRefundItem::class);
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }
}
