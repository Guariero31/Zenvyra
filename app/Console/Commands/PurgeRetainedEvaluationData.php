<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TenantEvaluationService;
use Illuminate\Console\Command;

class PurgeRetainedEvaluationData extends Command
{
    protected $signature = 'tenants:purge-evaluation-data';

    protected $description = 'Apaga schemas de lojas cuja retenção pós-avaliação já expirou';

    public function handle(TenantEvaluationService $evaluationService): int
    {
        $purged = $evaluationService->purgeDueTenants();

        $this->info("Lojas com dados apagados: {$purged}.");

        return self::SUCCESS;
    }
}
