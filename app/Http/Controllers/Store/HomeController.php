<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

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
            ->with(['primaryImage', 'category', 'variations'])
            ->latest()
            ->limit(4)
            ->get();

        return view('store.home', array_merge($this->shared(), [
            'carouselSlides' => $carouselSlides,
            'featured' => $featured,
        ]));
    }
}
