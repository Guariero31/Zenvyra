<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Tenant;
use App\Services\TenantSchemaManager;
use Illuminate\Support\Str;

class TenantObserver
{
    public function __construct(private TenantSchemaManager $schemaManager) {}

    /**
     * Gera o te_slug e o te_schema antes de persistir, para que a loja já nasça
     * com identificador de URL e nome de schema definidos.
     */
    public function creating(Tenant $tenant): void
    {
        if (blank($tenant->te_slug)) {
            $tenant->te_slug = Str::slug($tenant->te_name);
        }

        if (blank($tenant->te_schema)) {
            $tenant->te_schema = 'tenant_'.str_replace('-', '_', $tenant->te_slug);
        }
    }

    /**
     * Com a loja já criada na central, provisiona fisicamente o schema da loja
     * no PostgreSQL e roda as migrations de tenant dentro dele.
     */
    public function created(Tenant $tenant): void
    {
        $this->schemaManager->createAndMigrate($tenant->te_schema);
    }
}
