<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Planos comerciais da plataforma (schema central). Idempotente por slug.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'avaliacao',
                'name' => 'Avaliação',
                'description' => 'Ambiente reduzido para testes comerciais sem compromisso.',
                'price' => 0,
                'billing_period' => 'monthly',
                'trial_days' => 0,
                'max_products' => 25,
                'max_users' => 1,
                'features' => ['usuarios' => '1', 'produtos' => '25', 'suporte' => 'Avaliação'],
            ],
            [
                'slug' => 'starter',
                'name' => 'Starter',
                'description' => 'Plano inicial para quem está começando.',
                'price' => 99.90,
                'billing_period' => 'monthly',
                'trial_days' => 7,
                'max_products' => 500,
                'max_users' => 2,
                'features' => ['usuarios' => '2', 'produtos' => '500', 'suporte' => 'E-mail'],
            ],
            [
                'slug' => 'pro',
                'name' => 'Pro',
                'description' => 'Para lojas em crescimento com mais volume.',
                'price' => 199.90,
                'billing_period' => 'monthly',
                'trial_days' => 7,
                'max_products' => 5000,
                'max_users' => 10,
                'features' => ['usuarios' => '10', 'produtos' => '5000', 'suporte' => 'Prioritário'],
            ],
            [
                'slug' => 'enterprise',
                'name' => 'Enterprise',
                'description' => 'Recursos completos para grandes operações.',
                'price' => 499.90,
                'billing_period' => 'monthly',
                'trial_days' => 14,
                'max_products' => null,
                'max_users' => null,
                'features' => ['usuarios' => 'Ilimitado', 'produtos' => 'Ilimitado', 'suporte' => 'Dedicado'],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                [...$plan, 'is_active' => true],
            );
        }
    }
}
