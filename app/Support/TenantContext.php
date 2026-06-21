<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant;
use Filament\Facades\Filament;

/**
 * Resolve o tenant ativo exclusivamente pelo contexto do Filament (URL do painel),
 * nunca pela sessão — evita vazamento entre lojas com múltiplas abas abertas.
 */
final class TenantContext
{
    public static function current(): ?Tenant
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant ? $tenant : null;
    }

    public static function applySearchPath(): void
    {
        $schema = self::current()?->te_schema;

        if (filled($schema)) {
            PgsqlTenantSearchPath::apply($schema);
        }
    }

    public static function applySearchPathForAppPanel(): void
    {
        if (Filament::getCurrentPanel()?->getId() !== 'app') {
            return;
        }

        self::applySearchPath();
    }
}
