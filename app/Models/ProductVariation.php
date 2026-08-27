<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProductVariationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariation extends Model
{
    /** @use HasFactory<ProductVariationFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
