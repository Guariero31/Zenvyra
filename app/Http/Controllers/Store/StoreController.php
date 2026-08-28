<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\TenantTheme;
use Illuminate\Http\Request;

abstract class StoreController extends Controller
{
    protected Tenant $tenant;

    protected TenantTheme $theme;

    public function __construct(Request $request)
    {
        $this->tenant = $request->attributes->get('public_store_tenant');
        $this->theme = TenantTheme::obterOuCriarParaTenant($this->tenant->getKey());
    }

    /**
     * Data shared by every storefront page, including the top announcement
     * bar (WhatsApp + ticker items) — rendered site-wide in store.layout,
     * not just on the home page.
     *
     * @return array<string, mixed>
     */
    protected function shared(): array
    {
        $config = $this->theme->store_config ?? [];
        $tickerItems = $config['ticker_items'] ?? [];

        $tickerTexts = count($tickerItems) > 0
            ? collect($tickerItems)->pluck('text')->filter()->values()
            : Category::has('products')->orderBy('name')->pluck('name');

        return [
            'tenant' => $this->tenant,
            'theme' => $this->theme,
            'tickerTexts' => $tickerTexts,
        ];
    }
}
