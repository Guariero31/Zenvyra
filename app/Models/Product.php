<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'price', 'cost_price', 'stock', 'low_stock_threshold', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('produto');
    }

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'sku',
        'price',
        'cost_price',
        'commission_rate',
        'stock',
        'low_stock_threshold',
        'status',
        'has_variations',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'stock' => 'integer',
            'low_stock_threshold' => 'integer',
            'has_variations' => 'boolean',
        ];
    }

    /**
     * Whether this product's stock/price is managed per-variation. This is
     * an explicit toggle (not derived from whether variation rows exist),
     * so the "Variações" tab can be revealed before the first variation is
     * created.
     */
    public function hasVariations(): bool
    {
        return (bool) $this->has_variations;
    }

    public function effectiveStock(): int
    {
        if ($this->hasVariations()) {
            if ($this->relationLoaded('variations')) {
                return (int) $this->variations->sum('stock');
            }

            return (int) $this->variations()->sum('stock');
        }

        return (int) $this->stock;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Images belonging to the product itself (not to a specific variation). */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->whereNull('product_variation_id');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->whereNull('product_variation_id')
            ->where('is_primary', true);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock(): bool
    {
        if ($this->hasVariations()) {
            $variations = $this->relationLoaded('variations') ? $this->variations : $this->variations()->get();

            return $variations->contains(fn (ProductVariation $variation): bool => $variation->isLowStock());
        }

        return $this->effectiveStock() <= (int) $this->low_stock_threshold;
    }

    /**
     * Min/max sale price across variations (falling back to the product's
     * own price for variations without an override). For products without
     * variations, min and max are both the product's price.
     *
     * @return array{min: float, max: float}
     */
    public function priceRange(): array
    {
        if (! $this->hasVariations()) {
            return ['min' => (float) $this->price, 'max' => (float) $this->price];
        }

        $variations = $this->relationLoaded('variations') ? $this->variations : $this->variations()->get();

        $prices = $variations->isEmpty()
            ? collect([(float) $this->price])
            : $variations->map(fn (ProductVariation $variation): float => (float) $variation->effectivePrice($this));

        return ['min' => (float) $prices->min(), 'max' => (float) $prices->max()];
    }
}
