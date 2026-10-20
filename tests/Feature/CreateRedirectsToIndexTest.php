<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\Admin\Resources\Plans\Pages\CreatePlan;
use App\Filament\Admin\Resources\Plans\PlanResource;
use App\Filament\App\Resources\Categories\CategoryResource;
use App\Filament\App\Resources\Categories\Pages\CreateCategory;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Filament's default CreateRecord redirects to the Edit page when one
 * exists. This project overrides that via RedirectsToIndexAfterCreate so
 * every "create" flow lands back on the list instead.
 */
class CreateRedirectsToIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_category_redirects_to_the_list_in_the_app_panel(): void
    {
        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $tenant = Tenant::create(['te_name' => 'Loja Redirect Teste']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();
        session(['tenant_id' => $tenant->getKey()]);

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Categoria Redirect',
                'slug' => 'categoria-redirect',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(CategoryResource::getUrl('index'));
    }

    public function test_creating_a_plan_redirects_to_the_list_in_the_admin_panel(): void
    {
        $admin = User::factory()->create(['is_platform_admin' => true]);

        $this->actingAs($admin, 'admin');
        Filament::setCurrentPanel('admin');
        Filament::bootCurrentPanel();

        Livewire::test(CreatePlan::class)
            ->fillForm([
                'name' => 'Plano Redirect',
                'slug' => 'plano-redirect',
                'price' => '99.90',
                'billing_period' => 'monthly',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(PlanResource::getUrl('index'));
    }
}
