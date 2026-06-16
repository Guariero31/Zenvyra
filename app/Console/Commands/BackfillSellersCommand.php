<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillSellersCommand extends Command
{
    protected $signature = 'sellers:backfill
        {--schema= : Executa o backfill em um schema específico}
        {--tenant= : ID ou slug do tenant}
        {--include-inactive : Inclui lojas inativas}';

    protected $description = 'Cria sellers a partir dos usuários que já venderam e vincula pedidos históricos.';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->warn('Driver sem schemas detectado. Executando backfill no banco atual.');
            $this->backfillCurrentSchema('users');

            return self::SUCCESS;
        }

        if ($schema = $this->option('schema')) {
            $this->backfillTenantSchema($schema);

            return self::SUCCESS;
        }

        $tenants = Tenant::query()
            ->when($identifier = $this->option('tenant'), fn ($query) => $query
                ->where('id', $identifier)
                ->orWhere('te_slug', $identifier))
            ->when(! $this->option('include-inactive'), fn ($query) => $query->where('te_ativo', true))
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('Nenhum tenant encontrado para backfill.');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $this->backfillTenantSchema($tenant->te_schema);
        }

        return self::SUCCESS;
    }

    private function backfillTenantSchema(string $schema): void
    {
        $original = config('database.connections.pgsql.search_path');
        $schema = $this->quote($schema);

        $this->info("Executando backfill de sellers no schema [{$schema}]...");

        config(['database.connections.pgsql.search_path' => "\"{$schema}\", public"]);
        DB::purge('pgsql');

        try {
            $this->backfillCurrentSchema('public.users');
        } finally {
            config(['database.connections.pgsql.search_path' => $original]);
            DB::purge('pgsql');
        }
    }

    private function backfillCurrentSchema(string $usersTable): void
    {
        DB::transaction(function () use ($usersTable): void {
            $userIds = DB::table('orders')
                ->whereNotNull('user_id')
                ->distinct()
                ->pluck('user_id');

            if ($userIds->isEmpty()) {
                $this->line('Nenhuma venda histórica com usuário encontrada.');

                return;
            }

            $names = DB::table($usersTable)
                ->whereIn('id', $userIds)
                ->pluck('name', 'id');

            foreach ($userIds as $userId) {
                $sellerId = DB::table('sellers')
                    ->where('user_id', $userId)
                    ->value('id');

                if (! $sellerId) {
                    $sellerId = DB::table('sellers')->insertGetId([
                        'name' => $names[$userId] ?? "Vendedor #{$userId}",
                        'user_id' => $userId,
                        'commission_rate' => 0,
                        'active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('orders')
                    ->where('user_id', $userId)
                    ->whereNull('seller_id')
                    ->update(['seller_id' => $sellerId]);
            }
        });

        $this->info('Backfill de sellers concluído.');
    }

    private function quote(string $schemaName): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $schemaName) ?? '';
    }
}
