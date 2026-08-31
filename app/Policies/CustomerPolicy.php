<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\TenantPortalRole;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->belongsToCurrentTenant($user);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->belongsToCurrentTenant($user);
    }

    public function create(User $user): bool
    {
        return $this->isManager($user);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->isManager($user);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->isManager($user);
    }

    private function isManager(User $user): bool
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Tenant) {
            return false;
        }

        $role = $user->roleInTenant($tenant);

        return in_array($role, [TenantPortalRole::AdministradorCliente, TenantPortalRole::Leader], true);
    }

    private function belongsToCurrentTenant(User $user): bool
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant && $user->roleInTenant($tenant) !== null;
    }
}
