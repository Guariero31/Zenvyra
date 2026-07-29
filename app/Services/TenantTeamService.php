<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TenantPortalRole;
use App\Mail\WelcomeMail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class TenantTeamService
{
    public function __construct(private readonly PlanLimitService $planLimits) {}

    /**
     * @param  array{name: string, email: string, password?: ?string, tu_papel: string, tu_rotulo_setor?: ?string, tu_commission_rate?: float|int|string|null}  $data
     */
    public function addMember(Tenant $tenant, array $data): User
    {
        $this->planLimits->assertCanAddUser($tenant);

        $email = mb_strtolower(trim($data['email']));
        $existing = User::query()->where('email', $email)->first();

        if ($existing instanceof User) {
            if ($tenant->users()->where('users.id', $existing->getKey())->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Este usuário já faz parte da equipe.',
                ]);
            }

            if ($existing->is_platform_admin) {
                throw ValidationException::withMessages([
                    'email' => 'Este e-mail pertence a um administrador da plataforma.',
                ]);
            }

            $existing->update([
                'name' => $data['name'],
                ...filled($data['password'] ?? null) ? ['password' => $data['password']] : [],
            ]);

            $user = $existing;
        } else {
            if (blank($data['password'] ?? null)) {
                throw ValidationException::withMessages([
                    'password' => 'Informe uma senha para o novo usuário.',
                ]);
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => $data['password'],
                'is_platform_admin' => false,
            ]);
        }

        $tenant->users()->attach($user, $this->pivotAttributes($data));

        $this->sendWelcomeIfAdmin($tenant, $user, $data['tu_papel']);

        return $user;
    }

    /**
     * @param  array{name?: string, password?: ?string, tu_papel: string, tu_rotulo_setor?: ?string, tu_commission_rate?: float|int|string|null}  $data
     */
    public function updateMember(Tenant $tenant, User $member, array $data): User
    {
        $this->assertMemberBelongsToTenant($tenant, $member);

        $currentRole = $member->roleInTenant($tenant);
        $newRole = $this->resolveRole($data['tu_papel']);

        if ($currentRole === TenantPortalRole::AdministradorCliente
            && $newRole !== TenantPortalRole::AdministradorCliente) {
            $this->assertTenantKeepsAdministrator($tenant, $member);
        }

        if (filled($data['name'] ?? null)) {
            $member->update(['name' => $data['name']]);
        }

        if (filled($data['password'] ?? null)) {
            $member->update(['password' => $data['password']]);
        }

        $tenant->users()->updateExistingPivot($member->getKey(), $this->pivotAttributes($data));

        return $member->fresh();
    }

    public function removeMember(Tenant $tenant, User $member, User $actor): void
    {
        $this->assertMemberBelongsToTenant($tenant, $member);

        if ($member->is($actor)) {
            throw ValidationException::withMessages([
                'member' => 'Você não pode se remover da equipe.',
            ]);
        }

        if ($member->roleInTenant($tenant) === TenantPortalRole::AdministradorCliente) {
            $this->assertTenantKeepsAdministrator($tenant, $member);
        }

        $tenant->users()->detach($member);
    }

    private function assertMemberBelongsToTenant(Tenant $tenant, User $member): void
    {
        if ($member->roleInTenant($tenant) === null) {
            throw ValidationException::withMessages([
                'member' => 'Usuário não pertence à equipe desta loja.',
            ]);
        }
    }

    private function assertTenantKeepsAdministrator(Tenant $tenant, User $except): void
    {
        $remaining = $tenant->users()
            ->wherePivot('tu_papel', TenantPortalRole::AdministradorCliente->value)
            ->where('users.id', '!=', $except->getKey())
            ->count();

        if ($remaining < 1) {
            throw ValidationException::withMessages([
                'tu_papel' => 'A loja precisa de pelo menos um administrador.',
            ]);
        }
    }

    /**
     * @param  array{tu_papel: string, tu_rotulo_setor?: ?string, tu_commission_rate?: float|int|string|null}  $data
     * @return array{tu_papel: string, tu_rotulo_setor: ?string, tu_commission_rate: float}
     */
    private function pivotAttributes(array $data): array
    {
        return [
            'tu_papel' => $this->resolveRole($data['tu_papel'])->value,
            'tu_rotulo_setor' => $data['tu_rotulo_setor'] ?? null,
            'tu_commission_rate' => (float) ($data['tu_commission_rate'] ?? 0),
        ];
    }

    /** Envia e-mail de boas-vindas quando o usuário é vinculado como AdministradorCliente. */
    private function sendWelcomeIfAdmin(Tenant $tenant, User $user, TenantPortalRole|string $role): void
    {
        if ($this->resolveRole($role) !== TenantPortalRole::AdministradorCliente) {
            return;
        }

        $panelUrl = url("/app/{$tenant->te_slug}");

        Mail::to($user->email)->send(new WelcomeMail(
            userName:  $user->name,
            storeName: $tenant->displayName(),
            panelUrl:  $panelUrl,
        ));
    }

    private function resolveRole(TenantPortalRole|string $role): TenantPortalRole
    {
        return $role instanceof TenantPortalRole ? $role : TenantPortalRole::from($role);
    }
}
