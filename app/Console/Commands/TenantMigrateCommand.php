<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantSchemaManager;
use Illuminate\Console\Command;

class TenantMigrateCommand extends Command
{
    protected $signature = 'tenant:migrate
        {--schema= : Roda as migrations para um schema específico}
        {--tenant= : ID ou slug do tenant a migrar}';

    protected $description = 'Roda/atualiza as migrations de tenant para um schema, um tenant ou todos os tenants ativos.';

    public function handle(TenantSchemaManager $schemaManager): int
    {
        if ($schema = $this->option('schema')) {
            $this->migrate($schemaManager, $schema);

            return self::SUCCESS;
        }

        if ($identifier = $this->option('tenant')) {
            $tenant = Tenant::query()
                ->where('id', $identifier)
                ->orWhere('te_slug', $identifier)
                ->first();

            if (! $tenant) {
                $this->error("Tenant [{$identifier}] não encontrado por ID ou slug.");

                return self::FAILURE;
            }

            $this->migrate($schemaManager, $tenant->te_schema);

            return self::SUCCESS;
        }

        $tenants = Tenant::query()->where('te_ativo', true)->get();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant ativo cadastrado.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $this->migrate($schemaManager, $tenant->te_schema);
        }

        return self::SUCCESS;
    }

    private function migrate(TenantSchemaManager $schemaManager, string $schema): void
    {
        $this->info("Migrando schema [{$schema}]...");
        $schemaManager->createAndMigrate($schema);
        $this->info("Schema [{$schema}] atualizado.");
    }
}
