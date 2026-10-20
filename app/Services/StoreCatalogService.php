<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class StoreCatalogService
{
    /** @return array<string, mixed> */
    public function catalog(?string $categorySlug, string $sort, string $search): array
    {
        $query = Product::where('status', 'active')
            ->with(['primaryImage', 'category', 'variations']);

        if ($search !== '') {
            $query->whereLike('title', '%'.$search.'%');
        }

        if ($categorySlug) {
            $query->whereHas('category', fn (Builder $categoryQuery): Builder => $categoryQuery->where('slug', $categorySlug));
        }

        match ($sort) {
            'menor_preco' => $query->orderBy('price'),
            'maior_preco' => $query->orderByDesc('price'),
            'nome' => $query->orderBy('title'),
            default => $query->latest(),
        };

        $categories = Category::whereHas('products', fn (Builder $productQuery): Builder => $productQuery->where('status', 'active'))
            ->orderBy('name')->get();

        return [
            'products' => $query->orderBy('id')->paginate(12)->withQueryString(),
            'categories' => $categories,
            'active' => $categorySlug ? $categories->firstWhere('slug', $categorySlug) : null,
            'sort' => $sort,
            'search' => $search,
        ];
    }
}
