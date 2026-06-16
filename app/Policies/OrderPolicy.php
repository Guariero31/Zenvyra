<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\TenantPortalRole;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;

class OrderPolicy
{
    public function refund(User $user, Order $order): bool
    {
        return $this->isManager($user);
    }

    public function cancel(User $user, Order $order): bool
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
}
