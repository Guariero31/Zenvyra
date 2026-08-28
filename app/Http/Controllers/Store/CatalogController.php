<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogController extends StoreController
{
    public function __invoke(Request $request): View
    {
        $categorySlug = $request->query('categoria');
        $sort = $request->query('ordenar', 'recentes');

        $query = Product::where('status', 'active')
            ->with(['primaryImage', 'category']);

        match ($sort) {
            'menor_preco' => $query->orderBy('price'),
            'maior_preco' => $query->orderByDesc('price'),
            'nome' => $query->orderBy('title'),
            default => $query->latest(),
        };

        if ($categorySlug) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::has('products')->orderBy('name')->get();
        $active = $categorySlug
            ? $categories->firstWhere('slug', $categorySlug)
            : null;

        return view('store.catalog', array_merge($this->shared(), [
            'products' => $products,
            'categories' => $categories,
            'active' => $active,
            'sort' => $sort,
        ]));
    }
}
