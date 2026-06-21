<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Admin\Resources\Administrators\Pages\CreateAdministrator;
use App\Filament\Admin\Resources\Administrators\Pages\EditAdministrator;
use App\Filament\Admin\Resources\Administrators\Pages\ListAdministrators;
use App\Filament\Admin\Resources\IdentidadeVisuals\Pages\EditIdentidadeVisual;
use App\Filament\Admin\Resources\Plans\Pages\CreatePlan;
use App\Filament\Admin\Resources\Plans\Pages\EditPlan;
use App\Filament\Admin\Resources\Plans\Pages\ListPlans;
use App\Filament\Admin\Resources\StoreOwners\Pages\CreateStoreOwner;
use App\Filament\Admin\Resources\StoreOwners\Pages\EditStoreOwner;
use App\Filament\Admin\Resources\StoreOwners\Pages\ListStoreOwners;
use App\Filament\Admin\Resources\Subscriptions\Pages\CreateSubscription;
use App\Filament\Admin\Resources\Subscriptions\Pages\EditSubscription;
use App\Filament\Admin\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Admin\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Admin\Resources\Tenants\Pages\EditTenant;
use App\Filament\Admin\Resources\Tenants\Pages\ListTenants;
use App\Models\IdentidadeVisual;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminResourcesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->admin = User::factory()->platformAdmin()->create();
        $this->actingAs($this->admin);
    }

    public function test_admin_identidade_visual_resource_page_can_load(): void
    {
        $registro = IdentidadeVisual::obterOuCriar();

        Livewire::test(EditIdentidadeVisual::class, ['record' => $registro->id])->assertOk();
    }

    public function test_admin_tenant_resource_pages_can_load(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Admin Teste']);

        Livewire::test(ListTenants::class)->assertOk();
        Livewire::test(CreateTenant::class)->assertOk();
        Livewire::test(EditTenant::class, ['record' => $tenant->id])->assertOk();
    }

    public function test_admin_administrator_resource_pages_can_load(): void
    {
        $administrator = User::factory()->platformAdmin()->create();

        Livewire::test(ListAdministrators::class)->assertOk();
        Livewire::test(CreateAdministrator::class)->assertOk();
        Livewire::test(EditAdministrator::class, ['record' => $administrator->id])->assertOk();
    }

    public function test_admin_store_owner_resource_pages_can_load(): void
    {
        $storeOwner = User::factory()->create();

        Livewire::test(ListStoreOwners::class)->assertOk();
        Livewire::test(CreateStoreOwner::class)->assertOk();
        Livewire::test(EditStoreOwner::class, ['record' => $storeOwner->id])->assertOk();
    }

    public function test_creating_administrator_sets_platform_admin_flag(): void
    {
        Livewire::test(CreateAdministrator::class)
            ->fillForm([
                'name' => 'Novo Admin',
                'email' => 'novo.admin@zenvyra.com.br',
                'password' => 'password',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(User::whereEmail('novo.admin@zenvyra.com.br')->value('is_platform_admin'));
    }

    public function test_creating_store_owner_does_not_set_platform_admin_flag(): void
    {
        Livewire::test(CreateStoreOwner::class)
            ->fillForm([
                'name' => 'Novo Lojista',
                'email' => 'novo.lojista@zenvyra.com.br',
                'password' => 'password',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertFalse(User::whereEmail('novo.lojista@zenvyra.com.br')->value('is_platform_admin'));
    }

    public function test_admin_plan_resource_pages_can_load(): void
    {
        $plan = Plan::create([
            'name' => 'Plano Teste',
            'slug' => 'plano-teste',
            'price' => 99.90,
            'billing_period' => 'monthly',
            'is_active' => true,
        ]);

        Livewire::test(ListPlans::class)->assertOk();
        Livewire::test(CreatePlan::class)->assertOk();
        Livewire::test(EditPlan::class, ['record' => $plan->id])->assertOk();
    }

    public function test_admin_subscription_resource_pages_can_load(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Assinatura']);
        $plan = Plan::create([
            'name' => 'Plano Assinatura',
            'slug' => 'plano-assinatura',
            'price' => 199.90,
            'billing_period' => 'monthly',
            'is_active' => true,
        ]);
        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        Livewire::test(ListSubscriptions::class)->assertOk();
        Livewire::test(CreateSubscription::class)->assertOk();
        Livewire::test(EditSubscription::class, ['record' => $subscription->id])->assertOk();
    }

    public function test_plan_status_can_be_toggled_from_edit_page(): void
    {
        $plan = Plan::factory()->create(['is_active' => true]);

        Livewire::test(EditPlan::class, ['record' => $plan->id])
            ->callAction('toggleStatus')
            ->assertNotified();

        $this->assertFalse($plan->refresh()->is_active);
    }

    public function test_tenant_status_can_be_toggled_from_edit_page(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Toggle']);

        Livewire::test(EditTenant::class, ['record' => $tenant->id])
            ->callAction('toggleStatus')
            ->assertNotified();

        $this->assertFalse($tenant->refresh()->te_ativo);
    }

    public function test_admin_flag_can_be_toggled_for_other_users(): void
    {
        $other = User::factory()->create(['is_platform_admin' => false]);

        Livewire::test(EditStoreOwner::class, ['record' => $other->id])
            ->callAction('toggleAdmin')
            ->assertNotified();

        $this->assertTrue($other->refresh()->is_platform_admin);
    }

    public function test_admin_cannot_toggle_their_own_admin_flag(): void
    {
        Livewire::test(EditAdministrator::class, ['record' => $this->admin->id])
            ->assertActionHidden('toggleAdmin');
    }

    public function test_creating_tenant_starts_evaluation_by_default(): void
    {
        Plan::factory()->create(['slug' => 'avaliacao', 'trial_days' => 0]);

        Livewire::test(CreateTenant::class)
            ->fillForm(['te_name' => 'Loja Nova Avaliacao'])
            ->call('create')
            ->assertHasNoFormErrors();

        $tenant = Tenant::firstWhere('te_name', 'Loja Nova Avaliacao');

        $this->assertNotNull($tenant);
        $this->assertTrue($tenant->hasActiveSubscription());
        $this->assertTrue($tenant->gestaoSettings()->moduloVendedoresAtivo());
    }
}
