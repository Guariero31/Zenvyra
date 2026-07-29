<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\PgsqlTenantSearchPath;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolve o tenant pelo slug na URL pública (/{slug}) e aplica o search_path
 * do schema correspondente. Aborta com 404 se a loja não existir ou estiver
 * inativa.
 */
class ResolvePublicStoreTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('slug');

        $tenant = Tenant::where('te_slug', $slug)
            ->where('te_ativo', true)
            ->first();

        abort_if($tenant === null, 404);

        $request->attributes->set('public_store_tenant', $tenant);

        if (filled($tenant->te_schema)) {
            PgsqlTenantSearchPath::apply($tenant->te_schema);
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        PgsqlTenantSearchPath::reset();
    }
}
