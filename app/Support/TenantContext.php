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
    /** Sobreposição manual — usada em testes e jobs de console. */
    private static ?Tenant $override = null;

    public static function current(): ?Tenant
    {
        if (self::$override !== null) {
            return self::$override;
        }

        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant ? $tenant : null;
    }

    /** Define um tenant manualmente (testes / jobs de console). */
    public static function set(Tenant $tenant): void
    {
        self::$override = $tenant;
    }

    /** Remove a sobreposição manual. */
    public static function clear(): void
    {
        self::$override = null;
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
