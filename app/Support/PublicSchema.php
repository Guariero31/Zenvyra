<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Verifica existência de tabelas no schema public, independentemente do
 * search_path ativo (ex.: contexto multi-tenant do Filament).
 */
final class PublicSchema
{
    public static function hasTable(string $table): bool
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return Schema::hasTable($table);
        }

        $result = DB::selectOne(
            'SELECT to_regclass(?) IS NOT NULL AS "exists"',
            ['public.'.$table]
        );

        return (bool) ($result->exists ?? false);
    }
}
