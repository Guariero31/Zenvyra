<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use App\Enums\TenantPortalRole;
use App\Models\Tenant;
use App\Models\User;

trait SeedsTenantVendedores
{
    /**
     * @param  array<int, array{name: string, email: string, commission: float|int}>  $vendedores
     */
    protected function attachVendedores(Tenant $tenant, array $vendedores): void
    {
        foreach ($vendedores as $vendedor) {
            $user = User::updateOrCreate(
                ['email' => $vendedor['email']],
                [
                    'name' => $vendedor['name'],
                    'password' => 'password',
                    'is_platform_admin' => false,
                ],
            );

            $tenant->users()->syncWithoutDetaching([
                $user->id => [
                    'tu_papel' => TenantPortalRole::Vendedor->value,
                    'tu_rotulo_setor' => 'Vendas',
                    'tu_commission_rate' => $vendedor['commission'],
                ],
            ]);
        }
    }
}
