<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Normalizes the `features` JSON on all plans to a flat string array.
 * Each entry is a human-readable feature label shown on the pricing section.
 * The old {key: value} structure was partially hardcoded in the landing view.
 */
return new class extends Migration
{
    public function up(): void
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

    public function down(): void
    {
        $plans = [
            'avaliacao' => json_encode(['usuarios' => '1',          'produtos' => '25',      'suporte' => 'Avaliação']),
            'starter' => json_encode(['usuarios' => '2',          'produtos' => '500',     'suporte' => 'E-mail']),
            'pro' => json_encode(['usuarios' => '10',         'produtos' => '5000',    'suporte' => 'Prioritário']),
            'enterprise' => json_encode(['usuarios' => 'Ilimitado',  'produtos' => 'Ilimitado', 'suporte' => 'Dedicado']),
        ];

        foreach ($plans as $slug => $features) {
            DB::table('plans')->where('slug', $slug)->update(['features' => $features]);
        }
    }
};
