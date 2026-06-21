<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\PgsqlTenantSearchPath;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica o search_path do tenant ativo do Filament logo após a loja ser
 * resolvida na URL. O schema vem sempre do tenant da rota, nunca da sessão.
 */
class SetTenantSearchPath
{
    public function handle(Request $request, Closure $next): Response
    {
        session()->forget(['tenant_id', 'tenant_schema']);

        TenantContext::applySearchPath();

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        PgsqlTenantSearchPath::reset();
    }
}
