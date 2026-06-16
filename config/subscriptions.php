<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Período de tolerância (grace period)
    |--------------------------------------------------------------------------
    |
    | Dias que uma assinatura permanece acessível após vencer (trial ou ciclo
    | pago) antes de ser efetivamente expirada e bloquear o acesso à loja.
    |
    */

    'grace_days' => (int) env('SUBSCRIPTION_GRACE_DAYS', 5),

    /*
    |--------------------------------------------------------------------------
    | Plano padrão para novas lojas
    |--------------------------------------------------------------------------
    |
    | Slug do plano usado para iniciar automaticamente a assinatura (trial ou
    | ativa, conforme o plano) quando uma loja é criada pelo painel master.
    | Se o plano não existir, a loja nasce sem assinatura e fica bloqueada até
    | uma assinatura ser criada manualmente.
    |
    */

    'default_plan_slug' => env('SUBSCRIPTION_DEFAULT_PLAN', 'starter'),

    /*
    |--------------------------------------------------------------------------
    | Duração padrão do teste (trial)
    |--------------------------------------------------------------------------
    |
    | Quantidade de dias usada para pré-preencher o "Fim do teste" ao criar uma
    | assinatura no painel master, contados a partir da data de início.
    |
    */

    'default_trial_days' => (int) env('SUBSCRIPTION_DEFAULT_TRIAL_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Ciclo de cobrança
    |--------------------------------------------------------------------------
    |
    | Intervalo fixo, em dias, entre cobranças. A próxima cobrança é sempre
    | calculada como a data de início somada a esse intervalo.
    |
    */

    'billing_cycle_days' => (int) env('SUBSCRIPTION_BILLING_CYCLE_DAYS', 30),

];
