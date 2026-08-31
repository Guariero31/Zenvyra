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
    public function viewAny(User $user): bool
    {
        return $this->belongsToCurrentTenant($user);
    }

    public function view(User $user, Order $order): bool
    {
        return $this->isManager($user) || $this->ownsOrder($user, $order);
    }

    public function create(User $user): bool
    {
        return $this->belongsToCurrentTenant($user);
    }

    public function update(User $user, Order $order): bool
    {
        return $this->isManager($user) || $this->ownsOrder($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        return $this->isManager($user);
    }

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

    private function belongsToCurrentTenant(User $user): bool
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant && $user->roleInTenant($tenant) !== null;
    }

    private function ownsOrder(User $user, Order $order): bool
    {
        if ((int) $order->user_id === (int) $user->id) {
            return true;
        }

        return $order->seller()
            ->where('user_id', $user->id)
            ->exists();
    }
}
