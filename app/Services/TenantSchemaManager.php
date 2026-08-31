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

    public function dropSchema(string $schemaName): void
    {
        if (! $this->usesPostgres()) {
            return;
        }

        DB::statement('DROP SCHEMA IF EXISTS "'.$this->quote($schemaName).'" CASCADE');
    }

    private function quote(string $schemaName): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $schemaName) ?? '';
    }

    private function usesPostgres(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }
}
