<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
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
        $this->theme  = TenantTheme::obterOuCriarParaTenant($this->tenant->getKey());
    }

    /** @return array<string, mixed> */
    protected function shared(): array
    {
        return [
            'tenant' => $this->tenant,
            'theme'  => $this->theme,
        ];
    }
}
