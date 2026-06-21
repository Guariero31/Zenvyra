<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;

class TenantEvaluationService
{
    public function __construct(private readonly TenantSchemaManager $schemaManager) {}

    public function purge(Tenant $tenant): void
    {
        if ($tenant->te_data_purged_at !== null) {
            return;
        }

        if (filled($tenant->te_schema)) {
            $this->schemaManager->dropSchema($tenant->te_schema);
        }

        $tenant->update([
            'te_data_purged_at' => now(),
            'te_data_retention_ends_at' => null,
            'te_ativo' => false,
        ]);
    }

    public function purgeDueTenants(): int
    {
        $purged = 0;

        Tenant::query()
            ->whereNotNull('te_data_retention_ends_at')
            ->whereNull('te_data_purged_at')
            ->where('te_data_retention_ends_at', '<=', now())
            ->each(function (Tenant $tenant) use (&$purged): void {
                $this->purge($tenant);
                $purged++;
            });

        return $purged;
    }

    public function hasRetainedData(Tenant $tenant): bool
    {
        return $tenant->te_data_retention_ends_at !== null
            && $tenant->te_data_purged_at === null;
    }
}
