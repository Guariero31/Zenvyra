<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OrderRefundItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderRefundItem extends Model
{
    /** @use HasFactory<OrderRefundItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_refund_id',
        'order_item_id',
        'quantity',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function refund(): BelongsTo
    {
        return $this->belongsTo(OrderRefund::class, 'order_refund_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
