<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\PgsqlTenantSearchPath;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica o search_path do tenant ativo do Filament logo após a loja ser
 * resolvida na rota. O ID e o schema ficam persistidos na sessão para que as
 * requisições assíncronas do Livewire reapliquem o mesmo contexto.
 */
class SetTenantSearchPath
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if ($tenant instanceof Tenant) {
            session([
                'tenant_id' => $tenant->getKey(),
                'tenant_schema' => $tenant->te_schema,
            ]);

            PgsqlTenantSearchPath::apply($tenant->te_schema);
        }

        return $next($request);
    }
}
