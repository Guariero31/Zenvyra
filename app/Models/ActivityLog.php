<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

/**
 * Model de auditoria customizado. Estende o Activity do Spatie mas reside
 * na tabela do schema ativo do tenant (definida pelo search_path do PostgreSQL).
 *
 * @property string|null $log_name
 * @property string $description
 * @property string|null $event
 * @property Model|null $subject
 * @property Model|null $causer
 * @property Collection $properties
 * @property string|null $batch_uuid
 */
class ActivityLog extends Activity
{
    protected $table = 'activity_log';
}
