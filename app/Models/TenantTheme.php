<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\NormalizaCoresHex;
use App\Support\PublicSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantTheme extends Model
{
    use NormalizaCoresHex;

    protected $table = 'tenant_themes';

    public function getTable(): string
    {
        if ($this->getConnection()->getDriverName() === 'pgsql') {
            return 'public.tenant_themes';
        }

        return $this->table;
    }

    protected $fillable = [
        'tenant_id',
        'nome_loja',
        'logo_path',
        'favicon_path',
        'store_config',
        'cor_primaria_light',
        'cor_sucesso_light',
        'cor_alerta_light',
        'cor_erro_light',
        'bg_page_light',
        'bg_content_light',
        'bg_sidebar_light',
        'bg_topbar_light',
        'cor_primaria_dark',
        'cor_sucesso_dark',
        'cor_alerta_dark',
        'cor_erro_dark',
        'bg_page_dark',
        'bg_content_dark',
        'bg_sidebar_dark',
        'bg_topbar_dark',
    ];

    protected $casts = [
        'store_config' => 'array',
    ];

    /**
     * Helper: returns a store_config key with a default.
     *
     * @param  mixed  $default
     * @return mixed
     */
    public function storeConfig(string $key, mixed $default = null): mixed
    {
        return data_get($this->store_config ?? [], $key, $default);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Returns the TenantTheme for the given tenant, creating it with defaults if
     * it doesn't exist yet. Uses schema-qualified table check for SQLite compat.
     */
    public static function obterOuCriarParaTenant(int|string $tenantId): self
    {
        if (! PublicSchema::hasTable('tenant_themes')) {
            return (new self)->forceFill(['tenant_id' => $tenantId, ...self::defaults($tenantId)]);
        }

        return static::firstOrCreate(
            ['tenant_id' => $tenantId],
            self::defaults($tenantId)
        );
    }

    /** @return array<string, mixed> */
    public static function defaults(int|string|null $tenantId = null): array
    {
        return [
            'tenant_id' => $tenantId,
            'nome_loja' => null,
            'logo_path' => null,
            'favicon_path' => null,
            'store_config' => null,
            'cor_primaria_light' => '#4F46E5',
            'cor_sucesso_light' => '#10B981',
            'cor_alerta_light' => '#F59E0B',
            'cor_erro_light' => '#F43F5E',
            'bg_page_light' => null,
            'bg_content_light' => null,
            'bg_sidebar_light' => null,
            'bg_topbar_light' => null,
            'cor_primaria_dark' => '#818CF8',
            'cor_sucesso_dark' => '#34D399',
            'cor_alerta_dark' => '#FCD34D',
            'cor_erro_dark' => '#FB7185',
            'bg_page_dark' => null,
            'bg_content_dark' => null,
            'bg_sidebar_dark' => null,
            'bg_topbar_dark' => null,
        ];
    }
}
