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
 * Cria lojas adicionais (além da Loja Demo), cada uma com um lojista vinculado
 * e uma assinatura. Ao criar o Tenant, o TenantObserver provisiona o schema
 * físico da loja no PostgreSQL. Idempotente por slug/e-mail.
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            ['name' => 'Moda Urbana', 'slug' => 'moda-urbana', 'plan' => 'pro', 'email' => 'contato@modaurbana.com.br'],
            ['name' => 'Tech House', 'slug' => 'tech-house', 'plan' => 'enterprise', 'email' => 'contato@techhouse.com.br'],
        ];

        foreach ($stores as $store) {
            $tenant = Tenant::firstOrCreate(
                ['te_slug' => $store['slug']],
                ['te_name' => $store['name']],
            );

            $plan = Plan::firstWhere('slug', $store['plan']);

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

            $owner = User::updateOrCreate(
                ['email' => $store['email']],
                [
                    'name' => $store['name'].' (Lojista)',
                    'password' => 'password',
                    'is_platform_admin' => false,
                ],
            );

            $tenant->users()->syncWithoutDetaching([
                $owner->id => [
                    'tu_papel' => TenantPortalRole::AdministradorCliente->value,
                    'tu_rotulo_setor' => 'Gerência',
                ],
            ]);
        }
    }
}
