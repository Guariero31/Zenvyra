<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variation_id',
        'quantity',
        'price_at_sale',
        'cost_at_sale',
        'commission_rate',
        'commission_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price_at_sale' => 'decimal:2',
            'cost_at_sale' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variation(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class, 'product_variation_id');
    }

    public function refundItems(): HasMany
    {
        return $this->hasMany(OrderRefundItem::class);
    }

    public function refundedQuantity(): int
    {
        return (int) $this->refundItems()->sum('quantity');
    }

    public function refundableQuantity(): int
    {
        return max(0, $this->quantity - $this->refundedQuantity());
    }
}
