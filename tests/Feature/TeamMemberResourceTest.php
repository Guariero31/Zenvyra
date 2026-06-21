<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\TeamMembers\Pages\CreateTeamMember;
use App\Filament\App\Resources\TeamMembers\Pages\EditTeamMember;
use App\Filament\App\Resources\TeamMembers\Pages\ListTeamMembers;
use App\Filament\App\Resources\TeamMembers\TeamMemberResource;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantGestaoSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamMemberResourceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->manager = User::factory()->create();
        $this->tenant->users()->attach($this->manager, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
            'tu_commission_rate' => 0,
        ]);

        $this->actingAs($this->manager);
        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
        Filament::bootCurrentPanel();
    }

    public function test_list_page_shows_create_action(): void
    {
        Livewire::test(ListTeamMembers::class)
            ->assertActionVisible('create');
    }

    public function test_manager_can_create_new_team_member(): void
    {
        Livewire::test(CreateTeamMember::class)
            ->fillForm([
                'name' => 'Carlos Vendas',
                'email' => 'carlos@loja.test',
                'password' => 'password123',
                'tu_papel' => TenantPortalRole::Vendedor->value,
                'tu_rotulo_setor' => 'Vendas',
                'tu_commission_rate' => 5,
            ])
            ->call('create')
            ->assertNotified();

        $member = User::where('email', 'carlos@loja.test')->first();

        $this->assertNotNull($member);
        $this->assertSame(TenantPortalRole::Vendedor, $member->roleInTenant($this->tenant));
        $this->assertSame('5.00', number_format((float) $member->commissionRateInTenant($this->tenant), 2, '.', ''));
    }

    public function test_manager_can_attach_existing_user_by_email(): void
    {
        $existing = User::factory()->create(['email' => 'existente@loja.test']);

        Livewire::test(CreateTeamMember::class)
            ->fillForm([
                'name' => 'Usuário Existente',
                'email' => 'existente@loja.test',
                'password' => '',
                'tu_papel' => TenantPortalRole::Vendedor->value,
                'tu_commission_rate' => 2,
            ])
            ->call('create')
            ->assertNotified();

        $this->assertSame(TenantPortalRole::Vendedor, $existing->fresh()->roleInTenant($this->tenant));
    }

    public function test_manager_can_list_and_edit_team_commission(): void
    {
        $seller = User::factory()->create(['name' => 'Ana Vendas']);
        $this->tenant->users()->attach($seller, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
            'tu_commission_rate' => 3,
        ]);

        Livewire::test(ListTeamMembers::class)->assertOk();
        Livewire::test(EditTeamMember::class, ['record' => $seller->id])
            ->fillForm(['tu_commission_rate' => 8.5])
            ->call('save')
            ->assertNotified();

        $this->assertSame('8.50', number_format((float) $seller->fresh()->commissionRateInTenant($this->tenant), 2, '.', ''));
    }

    public function test_manager_can_remove_team_member(): void
    {
        $seller = User::factory()->create();
        $this->tenant->users()->attach($seller, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
        ]);

        Livewire::test(EditTeamMember::class, ['record' => $seller->id])
            ->callAction('delete')
            ->assertRedirect(TeamMemberResource::getUrl('index'));

        $this->assertNull($seller->fresh()->roleInTenant($this->tenant));
    }

    public function test_manager_cannot_remove_themselves(): void
    {
        Livewire::test(EditTeamMember::class, ['record' => $this->manager->id])
            ->assertActionHidden('delete');
    }

    public function test_vendedor_cannot_access_team_resource(): void
    {
        $seller = User::factory()->create();
        $this->tenant->users()->attach($seller, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
        ]);

        $this->actingAs($seller);
        Filament::setTenant($this->tenant);

        $this->assertFalse(TeamMemberResource::canViewAny());
    }

    public function test_team_resource_stays_available_when_commissions_module_is_disabled(): void
    {
        $this->tenant->update([
            'te_gestao_settings' => TenantGestaoSettings::from([
                'modulo_vendedores_ativo' => false,
            ])->toArray(),
        ]);

        $this->assertTrue(TeamMemberResource::canViewAny());
    }

    public function test_create_respects_plan_user_limit(): void
    {
        $plan = Plan::factory()->create(['max_users' => 1]);
        Subscription::factory()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $plan->id,
        ]);

        Livewire::test(CreateTeamMember::class)
            ->fillForm([
                'name' => 'Extra',
                'email' => 'extra@loja.test',
                'password' => 'password123',
                'tu_papel' => TenantPortalRole::Vendedor->value,
            ])
            ->call('create')
            ->assertNotified();

        $this->assertNull(User::where('email', 'extra@loja.test')->first());
    }
}
