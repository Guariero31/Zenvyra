<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends StoreController
{
    public function __invoke(string $slug): Response
    {
        $products = Product::where('status', 'active')
            ->select(['id', 'updated_at'])
            ->orderBy('id')
            ->get();

        $urls = collect([
            [
                'loc' => route('store.home', $slug),
                'lastmod' => now()->toDateString(),
                'priority' => '1.0',
                'changefreq' => 'daily',
            ],
            [
                'loc' => route('store.catalog', $slug),
                'lastmod' => now()->toDateString(),
                'priority' => '0.9',
                'changefreq' => 'daily',
            ],
            [
                'loc' => route('store.contact', $slug),
                'lastmod' => now()->toDateString(),
                'priority' => '0.5',
                'changefreq' => 'monthly',
            ],
        ])->concat(
            $products->map(fn (Product $p) => [
                'loc' => route('store.product', [$slug, $p->id]),
                'lastmod' => $p->updated_at->toDateString(),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ])
        );

        $xml = view('store.sitemap', ['urls' => $urls])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
