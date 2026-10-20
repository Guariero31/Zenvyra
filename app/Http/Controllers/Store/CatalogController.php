<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Requests\StoreCatalogRequest;
use App\Services\StoreCatalogService;
use Illuminate\Contracts\View\View;

class CatalogController extends StoreController
{
    public function __invoke(StoreCatalogRequest $request, StoreCatalogService $catalogService): View
    {
        return view('store.catalog', array_merge($this->shared(), $catalogService->catalog(
            $request->validated('categoria'),
            $request->validated('ordenar') ?? 'recentes',
            trim($request->validated('busca') ?? ''),
        )));
    }
}
