<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Conta os itens de catálogo da categoria: cada variação conta como um
     * item, e um produto sem variações conta como 1. Requer que `products`
     * esteja eager-loaded com a contagem de `variations` (ver
     * CategoriesTable::configure()), senão dispara consultas extras.
     */
    public function catalogItemsCount(): int
    {
        return $this->products->sum(
            fn (Product $product): int => $product->variations_count > 0 ? $product->variations_count : 1,
        );
    }
}
