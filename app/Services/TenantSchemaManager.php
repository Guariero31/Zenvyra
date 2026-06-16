<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Provisiona e mantém os schemas físicos dos tenants no PostgreSQL. Cada loja
 * tem seu próprio schema isolado, populado exclusivamente pelas migrations de
 * database/migrations/tenant.
 */
class TenantSchemaManager
{
    private const TENANT_MIGRATIONS_PATH = 'database/migrations/tenant';

    public function createSchema(string $schemaName): void
    {
        if (! $this->usesPostgres()) {
            return;
        }

        DB::statement('CREATE SCHEMA IF NOT EXISTS "'.$this->quote($schemaName).'"');
    }

    public function migrateSchema(string $schemaName): void
    {
        if (! $this->usesPostgres()) {
            return;
        }

        $original = config('database.connections.pgsql.search_path');

        // Durante a migration o search_path aponta APENAS para o schema do
        // tenant, garantindo que tanto as tabelas quanto a própria tabela de
        // controle "migrations" sejam criadas dentro dele (e não no public).
        config(['database.connections.pgsql.search_path' => '"'.$this->quote($schemaName).'"']);
        DB::purge('pgsql');

        try {
            Artisan::call('migrate', [
                '--database' => 'pgsql',
                '--path' => self::TENANT_MIGRATIONS_PATH,
                '--force' => true,
            ]);
        } finally {
            config(['database.connections.pgsql.search_path' => $original]);
            DB::purge('pgsql');
        }
    }

    public function createAndMigrate(string $schemaName): void
    {
        $this->createSchema($schemaName);
        $this->migrateSchema($schemaName);
    }

    private function quote(string $schemaName): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $schemaName) ?? '';
    }

    /**
     * O isolamento físico por schema é uma característica exclusiva do
     * PostgreSQL. Em outros drivers (ex.: SQLite nos testes) o provisionamento
     * é ignorado.
     */
    private function usesPostgres(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }
}
