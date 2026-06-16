<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderOrigin;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'origin',
        'total_amount',
        'payment_method',
        'status',
        'inventory_deducted_at',
        'refunded_amount',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'origin' => OrderOrigin::class,
            'status' => OrderStatus::class,
            'total_amount' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'inventory_deducted_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Vendedor responsável pela venda. Vive no schema central (public.users).
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(OrderRefund::class);
    }

    public function netAmount(): string
    {
        return bcsub((string) $this->total_amount, (string) $this->refunded_amount, 2);
    }
}
