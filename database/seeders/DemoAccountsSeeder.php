<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SubscriptionStatus;
use App\Enums\TenantPortalRole;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Cria contas iniciais para os testes manuais: um administrador master da
 * plataforma e uma loja demo com um lojista vinculado. Idempotente.
 */
class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $master = User::updateOrCreate(
            ['email' => 'master@zenvyra.com.br'],
            [
                'name' => 'Admin Master',
                'password' => 'password',
                'is_platform_admin' => true,
            ],
        );

        $tenant = Tenant::firstOrCreate(
            ['te_slug' => 'loja-demo'],
            ['te_name' => 'Loja Demo'],
        );

        $plan = Plan::firstWhere('slug', 'starter');

        if ($plan) {
            $cycleEnd = now()->addDays((int) config('subscriptions.billing_cycle_days'));

            Subscription::updateOrCreate(
                ['tenant_id' => $tenant->id, 'plan_id' => $plan->id],
                [
                    'status' => SubscriptionStatus::Active,
                    'starts_at' => now(),
                    'ends_at' => $cycleEnd,
                    'next_billing_at' => $cycleEnd,
                    'grace_ends_at' => $cycleEnd->copy()->addDays((int) config('subscriptions.grace_days')),
                ],
            );
        }

        $lojista = User::updateOrCreate(
            ['email' => 'lojista@zenvyra.com.br'],
            [
                'name' => 'Lojista Demo',
                'password' => 'password',
                'is_platform_admin' => false,
            ],
        );

        $tenant->users()->syncWithoutDetaching([
            $lojista->id => [
                'tu_papel' => TenantPortalRole::AdministradorCliente->value,
                'tu_rotulo_setor' => 'Gerência',
            ],
        ]);
    }
}
