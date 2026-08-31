<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentidadeVisualHistorico extends Model
{
    public $timestamps = false;

    protected $table = 'identidade_visual_historico';

    public function getTable(): string
    {
        if ($this->getConnection()->getDriverName() === 'pgsql') {
            return 'public.identidade_visual_historico';
        }

        return $this->table;
    }

    protected $fillable = [
        'user_id',
        'painel',
        'tenant_id',
        'alteracoes',
        'criado_em',
    ];

    protected function casts(): array
    {
        return [
            'alteracoes' => 'array',
            'criado_em' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Records only the fields that actually changed between $anterior and $novo.
     * No-ops when nothing changed.
     *
     * @param  array<string, mixed>  $anterior  Values before saving
     * @param  array<string, mixed>  $novo  Values after saving
     */
    public static function registrar(
        string $painel,
        array $anterior,
        array $novo,
        int|string|null $tenantId = null,
        int|string|null $userId = null,
    ): void {
        $alteracoes = [];

        foreach ($novo as $campo => $valorNovo) {
            $valorAntigo = $anterior[$campo] ?? null;

            if ((string) $valorAntigo !== (string) ($valorNovo ?? '')) {
                $alteracoes[$campo] = [
                    'de' => $valorAntigo,
                    'para' => $valorNovo,
                ];
            }
        }

        if (empty($alteracoes)) {
            return;
        }

        static::create([
            'user_id' => $userId ?? auth()->id(),
            'painel' => $painel,
            'tenant_id' => $tenantId,
            'alteracoes' => $alteracoes,
            'criado_em' => now(),
        ]);
    }
}
