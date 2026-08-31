<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orquestra a carga completa: planos e contas na central (public) e, em seguida,
 * catálogo e vendas dentro do schema físico de cada loja.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            DemoAccountsSeeder::class,
            TenantSeeder::class,
            TenantDataSeeder::class,
        ]);
    }
}
