<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\TenantPortalRole;
use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;

class ActivityLogPolicy
{
    /**
     * Apenas administradores e líderes podem consultar o histórico de auditoria.
     */
    public function viewAny(User $user): bool
    {
        return $this->isManager($user);
    }

    public function view(User $user, ActivityLog $log): bool
    {
        return $this->isManager($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ActivityLog $log): bool
    {
        return false;
    }

    public function delete(User $user, ActivityLog $log): bool
    {
        return false;
    }

    private function isManager(User $user): bool
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Tenant) {
            return false;
        }

        return in_array($user->roleInTenant($tenant), [
            TenantPortalRole::AdministradorCliente,
            TenantPortalRole::Leader,
        ], true);
    }
}
