<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Loja/empresa cadastrada na central (te_tenants). Cada tenant possui um schema
 * físico isolado no PostgreSQL, identificado por te_schema. O Filament resolve
 * o tenant na URL através do te_slug.
 */
class Tenant extends Model implements HasName
{
    /** @use HasFactory<\Database\Factories\TenantFactory> */
    use HasFactory;

    protected $table = 'te_tenants';

    protected $fillable = [
        'te_name',
        'te_slug',
        'te_schema',
        'te_ativo',
        'te_gestao_settings',
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

    public function getFilamentName(): string
    {
        return $this->te_name;
    }
}
