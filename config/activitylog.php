<?php

declare(strict_types=1);

return [

    /*
     * Se false, nenhuma atividade é gravada no banco.
     */
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),

    /*
     * Registros mais antigos que este número de dias serão removidos
     * pelo comando activity:clean.
     */
    'delete_records_older_than_days' => 365,

    'default_log_name' => 'default',

    'default_auth_driver' => null,

    'subject_returns_soft_deleted_models' => false,

    /*
     * Model customizado que armazena as atividades no schema do tenant.
     */
    'activity_model' => \App\Models\ActivityLog::class,

    /*
     * Nome da tabela de logs — criada dentro de cada schema de tenant.
     */
    'table_name' => env('ACTIVITY_LOGGER_TABLE_NAME', 'activity_log'),

    /*
     * Conexão padrão do Laravel — o search_path do tenant direciona para
     * o schema correto em tempo de execução.
     */
    'database_connection' => env('ACTIVITY_LOGGER_DB_CONNECTION'),

];
