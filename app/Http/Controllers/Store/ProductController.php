<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductController extends StoreController
{
    public function __invoke(string $slug, int $product): View
    {
        $product = Product::where('status', 'active')
            ->with(['category', 'primaryImage', 'images', 'variations'])
            ->findOrFail($product);

        $related = Product::where('status', 'active')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['primaryImage', 'category', 'variations'])
            ->limit(4)
            ->get();

        return view('store.product', array_merge($this->shared(), [
            'product' => $product,
            'related' => $related,
        ]));
    }
}
