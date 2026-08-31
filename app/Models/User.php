<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TenantPortalRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

/**
 * Usuário global da plataforma. Vive no schema central (public.users) e pode
 * estar vinculado a vários tenants através da pivot tu_vinculos.
 */
class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_platform_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_platform_admin' => 'boolean',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tu_vinculos')
            ->withPivot(['tu_papel', 'tu_rotulo_setor', 'tu_commission_rate'])
            ->withTimestamps();
    }

    public function getTenants(Panel $panel): array|Collection
    {
        return $this->tenants;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->tenants()->where('te_tenants.id', $tenant->getKey())->exists();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->is_platform_admin,
            'app' => $this->tenants()->exists(),
            default => false,
        };
    }

    public function roleInTenant(Tenant $tenant): ?TenantPortalRole
    {
        $pivot = $this->tenants()
            ->where('te_tenants.id', $tenant->getKey())
            ->first()
            ?->pivot;

        if (! $pivot?->tu_papel) {
            return null;
        }

        return TenantPortalRole::from($pivot->tu_papel);
    }

    public function commissionRateInTenant(Tenant $tenant): string
    {
        $pivot = $this->tenants()
            ->where('te_tenants.id', $tenant->getKey())
            ->first()
            ?->pivot;

        return (string) ($pivot->tu_commission_rate ?? 0);
    }
}
