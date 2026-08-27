<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Keeps only "extra" features in the JSON — capacity labels (products/users/trial)
 * are now rendered dynamically from max_products, max_users and trial_days fields.
 */
return new class extends Migration
{
    public function up(): void
    {
        $plans = [
            'avaliacao' => json_encode(['Suporte por e-mail']),
            'starter' => json_encode(['Suporte por e-mail']),
            'pro' => json_encode(['Suporte prioritário', 'Relatórios avançados']),
            'enterprise' => json_encode([
                'Suporte dedicado',
                'SLA garantido em contrato',
                'API de integração',
                'Onboarding personalizado',
            ]),
        ];

        foreach ($plans as $slug => $features) {
            DB::table('plans')->where('slug', $slug)->update(['features' => $features]);
        }
    }

    public function down(): void
    {
        $plans = [
            'avaliacao' => json_encode([
                'Até 25 produtos',
                'Até 1 usuário',
                'Suporte por e-mail',
            ]),
            'starter' => json_encode([
                'Até 500 produtos',
                'Até 2 usuários',
                '7 dias grátis para testar',
                'Suporte por e-mail',
            ]),
            'pro' => json_encode([
                'Até 5.000 produtos',
                'Até 10 usuários',
                '7 dias grátis para testar',
                'Suporte prioritário',
                'Relatórios avançados',
            ]),
            'enterprise' => json_encode([
                'Produtos ilimitados',
                'Usuários ilimitados',
                '14 dias grátis para testar',
                'Suporte dedicado',
                'SLA garantido em contrato',
                'API de integração',
                'Onboarding personalizado',
            ]),
        ];

        foreach ($plans as $slug => $features) {
            DB::table('plans')->where('slug', $slug)->update(['features' => $features]);
        }
    }
};
