<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\NormalizaCoresHex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class IdentidadeVisual extends Model
{
    use NormalizaCoresHex;

    protected $table = 'identidade_visual';

    /**
     * In PostgreSQL, schema-qualify the table to prevent the tenant search_path
     * from accidentally resolving to a tenant schema table with the same name.
     * SQLite (tests) does not support schema prefixes, so we skip it there.
     */
    public function getTable(): string
    {
        if ($this->getConnection()->getDriverName() === 'pgsql') {
            return 'public.identidade_visual';
        }

        return $this->table;
    }

    protected $fillable = [
        'nome_sistema',
        'cor_primaria',
        'cor_sucesso',
        'cor_alerta',
        'cor_erro',
        'cor_primaria_dark',
        'cor_sucesso_dark',
        'cor_alerta_dark',
        'cor_erro_dark',
        'bg_page_light',
        'bg_content_light',
        'bg_sidebar_light',
        'bg_topbar_light',
        'bg_page_dark',
        'bg_content_dark',
        'bg_sidebar_dark',
        'bg_topbar_dark',
        'logo_path',
        'favicon_path',
    ];

    /** Returns the singleton master record, or null when the table does not exist yet. */
    public static function obterOuCriar(): self
    {
        if (! Schema::hasTable('identidade_visual')) {
            return (new self)->forceFill(self::defaults());
        }

        return static::firstOrCreate([], self::defaults());
    }

    public static function defaults(): array
    {
        return [
            'nome_sistema' => 'Zenvyra · Master',
            'cor_primaria' => '#4F46E5',
            'cor_sucesso' => '#10B981',
            'cor_alerta' => '#F59E0B',
            'cor_erro' => '#F43F5E',
            'cor_primaria_dark' => '#818CF8',
            'cor_sucesso_dark' => '#34D399',
            'cor_alerta_dark' => '#FCD34D',
            'cor_erro_dark' => '#FB7185',
            'bg_page_light' => null,
            'bg_content_light' => null,
            'bg_sidebar_light' => null,
            'bg_topbar_light' => null,
            'bg_page_dark' => null,
            'bg_content_dark' => null,
            'bg_sidebar_dark' => null,
            'bg_topbar_dark' => null,
            'logo_path' => null,
            'favicon_path' => null,
        ];
    }
}
