<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SellerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    /** @use HasFactory<SellerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'commission_rate',
        'active',
    ];

    protected $attributes = [
        'commission_rate' => 0,
        'active' => true,
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeLinkedToUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
