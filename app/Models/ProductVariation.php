<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductVariationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariation extends Model
{
    /** @use HasFactory<ProductVariationFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'stock',
        'price',
        'low_stock_threshold',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'price' => 'decimal:2',
            'low_stock_threshold' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * The price to charge when this variation is sold. Falls back to the
     * parent product's price when the variation has no override — pass an
     * already-loaded $product to avoid an extra query.
     */
    public function effectivePrice(?Product $product = null): float
    {
        if ($this->price !== null) {
            return (float) $this->price;
        }

        $product ??= $this->relationLoaded('product') ? $this->product : $this->product()->first();

        return (float) ($product?->price ?? 0);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->low_stock_threshold;
    }
}
