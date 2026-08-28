<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends StoreController
{
    public function __invoke(): View
    {
        $config = $this->theme->store_config ?? [];

        // Carousel: prefer manually configured slides, fall back to featured products
        $carouselSlides = $config['carousel_slides'] ?? [];

        // Featured products (always shown below the carousel)
        $featured = Product::where('status', 'active')
            ->with('primaryImage')
            ->latest()
            ->limit(4)
            ->get();

        // Ticker: prefer manually configured items, fall back to category names
        $tickerItems = $config['ticker_items'] ?? [];
        $categories = Category::has('products')->orderBy('name')->get();

        return view('store.home', array_merge($this->shared(), [
            'carouselSlides' => $carouselSlides,
            'featured' => $featured,
            'tickerItems' => $tickerItems,
            'categories' => $categories,
        ]));
    }
}
