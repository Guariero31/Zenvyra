<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\TenantPortalRole;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;

class TeamMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isManager($user) || $this->isHr($user);
    }

    public function view(User $user, User $member): bool
    {
        return ($this->isManager($user) || $this->isHr($user))
            && $this->memberBelongsToCurrentTenant($member);
    }

    public function create(User $user): bool
    {
        return $this->isManager($user) || $this->isHr($user);
    }

    public function update(User $user, User $member): bool
    {
        return $this->view($user, $member);
    }

    /**
     * Apenas administradores podem remover membros.
     * RH pode gerir mas não demitir.
     */
    public function delete(User $user, User $member): bool
    {
        if (! $this->isManager($user)) {
            return false;
        }

        if (! $this->memberBelongsToCurrentTenant($member)) {
            return false;
        }

        return ! $member->is($user);
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

    private function isHr(User $user): bool
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Tenant) {
            return false;
        }

        return $user->roleInTenant($tenant) === TenantPortalRole::Rh;
    }

    private function memberBelongsToCurrentTenant(User $member): bool
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant
            && $member->roleInTenant($tenant) !== null;
    }
}
