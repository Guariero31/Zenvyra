<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BillingPeriod;
use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_period',
        'trial_days',
        'max_products',
        'max_users',
        'is_active',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'billing_period' => BillingPeriod::class,
            'trial_days' => 'integer',
            'max_products' => 'integer',
            'max_users' => 'integer',
            'is_active' => 'boolean',
            'features' => 'array',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasTrial(): bool
    {
        return $this->trial_days > 0;
    }
}
