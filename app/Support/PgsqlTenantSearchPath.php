<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Altera o contexto de schema (search_path) da conexão PostgreSQL ativa. O
 * schema do tenant é resolvido primeiro e o public permanece como fallback para
 * as tabelas centrais (users, te_tenants, sessions, cache...).
 */
class PgsqlTenantSearchPath
{
    public static function apply(string $schema): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('SET search_path TO "'.self::sanitize($schema).'", public');
    }

    public static function reset(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('SET search_path TO public');
    }

    private static function sanitize(string $schema): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $schema) ?? '';
    }
}
