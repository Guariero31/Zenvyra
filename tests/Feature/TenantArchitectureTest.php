<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_observer_generates_slug_and_schema_from_name(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Do João']);

        $this->assertSame('loja-do-joao', $tenant->te_slug);
        $this->assertSame('tenant_loja_do_joao', $tenant->te_schema);
    }

    public function test_user_belongs_to_many_tenants_with_pivot(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['te_name' => 'Loja Teste']);

        $tenant->users()->attach($user, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
            'tu_rotulo_setor' => 'Diretoria',
        ]);

        $linked = $user->tenants()->first();

        $this->assertTrue($user->tenants->contains($tenant));
        $this->assertSame(TenantPortalRole::AdministradorCliente->value, $linked->pivot->tu_papel);
        $this->assertSame('Diretoria', $linked->pivot->tu_rotulo_setor);
    }

    public function test_can_access_tenant_only_when_linked(): void
    {
        $user = User::factory()->create();
        $linkedTenant = Tenant::create(['te_name' => 'Loja Vinculada']);
        $otherTenant = Tenant::create(['te_name' => 'Loja Alheia']);

        $linkedTenant->users()->attach($user, ['tu_papel' => TenantPortalRole::Member->value]);

        $this->assertTrue($user->canAccessTenant($linkedTenant));
        $this->assertFalse($user->canAccessTenant($otherTenant));
    }

    public function test_only_platform_admin_can_access_admin_panel(): void
    {
        $adminPanel = Filament::getPanel('admin');

        $this->assertTrue(User::factory()->platformAdmin()->create()->canAccessPanel($adminPanel));
        $this->assertFalse(User::factory()->create()->canAccessPanel($adminPanel));
    }

    public function test_app_panel_requires_a_tenant_link(): void
    {
        $appPanel = Filament::getPanel('app');

        $linked = User::factory()->create();
        Tenant::create(['te_name' => 'Loja Do Lojista'])->users()->attach($linked, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
        ]);

        $this->assertTrue($linked->canAccessPanel($appPanel));
        $this->assertFalse(User::factory()->create()->canAccessPanel($appPanel));
    }

    public function test_tenant_portal_role_enum_exposes_labels(): void
    {
        $this->assertSame('Administrador da Loja', TenantPortalRole::AdministradorCliente->getLabel());
        $this->assertSame('Colaborador', TenantPortalRole::Member->getLabel());
    }
}
