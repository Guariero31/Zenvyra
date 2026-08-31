<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Scopes\TenantMemberOperatorScope;
use App\Models\Tenant;
use App\Services\TenantOrderPurgeService;
use App\Support\PgsqlTenantSearchPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairTenantOrderIsolation extends Command
{
    protected $signature = 'tenants:repair-order-isolation {--dry-run : Apenas lista pedidos inválidos, sem excluir}';

    protected $description = 'Remove pedidos cujo vendedor não pertence à loja do schema PostgreSQL';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->error('Este comando requer PostgreSQL.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $totalRemoved = 0;
        $purger = app(TenantOrderPurgeService::class);

        foreach (Tenant::query()->orderBy('te_slug')->get() as $tenant) {
            PgsqlTenantSearchPath::apply($tenant->te_schema);

            $memberIds = $tenant->users()->pluck('users.id');

            $invalidOrders = Order::withoutGlobalScope(TenantMemberOperatorScope::class)
                ->whereNotNull('user_id')
                ->whereNotIn('user_id', $memberIds)
                ->get();

            if ($invalidOrders->isEmpty()) {
                $this->line("{$tenant->te_slug}: OK");

                continue;
            }

            $this->warn("{$tenant->te_slug}: {$invalidOrders->count()} pedido(s) com vendedor externo");

            if ($dryRun) {
                foreach ($invalidOrders as $order) {
                    $this->line("  - #{$order->id} (user_id={$order->user_id})");
                }

                continue;
            }

            foreach ($invalidOrders as $order) {
                $purger->purge($order);
                $totalRemoved++;
            }
        }

        PgsqlTenantSearchPath::reset();

        if ($dryRun) {
            $this->info('Dry-run concluído. Nenhum registro foi excluído.');
        } else {
            $this->info("Reparo concluído. {$totalRemoved} pedido(s) removido(s).");
            $this->comment('Execute php artisan db:seed --class=TenantDataSeeder para repor dados demo.');
        }

        return self::SUCCESS;
    }
}
