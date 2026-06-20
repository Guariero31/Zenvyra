<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TenantFactory;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Loja/empresa cadastrada na central (te_tenants). Cada tenant possui um schema
 * físico isolado no PostgreSQL, identificado por te_schema. O Filament resolve
 * o tenant na URL através do te_slug.
 */
class Tenant extends Model implements HasName
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    protected $table = 'te_tenants';

    /**
     * In PostgreSQL, schema-qualify to prevent the tenant search_path from
     * accidentally resolving to a tenant-schema table with the same name.
     * SQLite (tests) does not support schema prefixes.
     */
    public function getTable(): string
    {
        if ($this->getConnection()->getDriverName() === 'pgsql') {
            return 'public.te_tenants';
        }

        return $this->table;
    }

    protected $fillable = [
        'te_name',
        'te_slug',
        'te_schema',
        'te_ativo',
        'te_gestao_settings',
        'brand_name',
        'brand_primary_color',
        'brand_logo_path',
    ];

    protected function casts(): array
    {
        return [
            'te_ativo' => 'boolean',
            'te_gestao_settings' => 'array',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tu_vinculos')
            ->withPivot(['tu_papel', 'tu_rotulo_setor'])
            ->withTimestamps();
    }

    public function theme(): HasOne
    {
        return $this->hasOne(TenantTheme::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Assinatura que atualmente libera o acesso (trial vigente, ativa ou em
     * tolerância). Retorna a mais recente quando houver mais de uma válida.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->latest('id')
            ->get()
            ->first(fn (Subscription $subscription): bool => $subscription->grantsAccess());
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Used by Filament's tenant picker. Prefers TenantTheme::nome_loja when the
     * relationship is already loaded; falls back to the legacy brand_name column
     * and then to the canonical te_name.
     */
    public function getFilamentName(): string
    {
        return $this->theme?->nome_loja ?? $this->brand_name ?? $this->te_name;
    }

    /** Returns the effective display name shown in the panel. */
    public function displayName(): string
    {
        return $this->theme?->nome_loja ?? $this->brand_name ?? $this->te_name;
    }
}
