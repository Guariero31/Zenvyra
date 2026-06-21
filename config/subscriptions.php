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

    /*
    |--------------------------------------------------------------------------
    | Avaliação comercial (ambiente reduzido)
    |--------------------------------------------------------------------------
    |
    | Duração padrão do período de teste para lojas criadas como avaliação e
    | prazo para manter os dados após o fim do acesso, antes da exclusão
    | automática do schema.
    |
    */

    'evaluation_days' => (int) env('SUBSCRIPTION_EVALUATION_DAYS', 20),

    'evaluation_data_retention_days' => (int) env('SUBSCRIPTION_EVALUATION_DATA_RETENTION_DAYS', 60),

    'evaluation_plan_slug' => env('SUBSCRIPTION_EVALUATION_PLAN', 'avaliacao'),

    /*
    |--------------------------------------------------------------------------
    | Alerta de avaliações expirando
    |--------------------------------------------------------------------------
    |
    | Janela em dias usada no widget do dashboard admin para listar lojas em
    | avaliação cujo prazo de teste termina em breve.
    |
    */

    'evaluation_expiring_alert_days' => (int) env('SUBSCRIPTION_EVALUATION_EXPIRING_ALERT_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Aviso de renovação no painel do lojista
    |--------------------------------------------------------------------------
    |
    | Quantos dias antes do vencimento (avaliação, teste ou ciclo pago) o banner
    | de alerta passa a ser exibido no painel /app.
    |
    */

    'renewal_notice_days' => (int) env('SUBSCRIPTION_RENEWAL_NOTICE_DAYS', 7),

];
