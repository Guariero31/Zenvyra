<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\Admin\Resources\IdentidadeVisuals\IdentidadeVisualResource;
use App\Filament\Admin\Resources\IdentidadeVisuals\Pages\EditIdentidadeVisual;
use App\Filament\App\Resources\TenantBrandings\Pages\EditTenantBranding;
use App\Filament\App\Resources\TenantBrandings\TenantBrandingResource;
use App\Models\IdentidadeVisual;
use App\Models\Tenant;
use App\Models\TenantTheme;
use App\Models\User;
use App\Services\BrandingService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class IdentidadeVisualTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_resource_registers_navigation_item(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs(User::factory()->platformAdmin()->create());

        $items = IdentidadeVisualResource::getNavigationItems();

        $this->assertCount(1, $items);
        $this->assertSame('Identidade Visual', $items[0]->getLabel());
    }

    public function test_master_resource_renders_and_persists_branding(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs(User::factory()->platformAdmin()->create());

        $registro = IdentidadeVisual::obterOuCriar();

        Livewire::test(EditIdentidadeVisual::class, ['record' => $registro->id])
            ->assertOk()
            ->assertSee('Identidade Visual — Painel Master')
            ->fillForm([
                'nome_sistema' => 'Zenvyra · Central',
                'cor_primaria' => '#111827',
                'cor_sucesso' => '#059669',
                'cor_alerta' => '#D97706',
                'cor_erro' => '#DC2626',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('identidade_visual', [
            'nome_sistema' => 'Zenvyra · Central',
            'cor_primaria' => '#111827',
            'cor_sucesso' => '#059669',
            'cor_alerta' => '#D97706',
            'cor_erro' => '#DC2626',
        ]);

        $branding = app(BrandingService::class);
        $this->assertSame('Zenvyra · Central', $branding->nomeMaster());
    }

    public function test_master_restore_defaults_updates_form_without_saving(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs(User::factory()->platformAdmin()->create());

        $registro = IdentidadeVisual::create([
            'nome_sistema' => 'Nome customizado',
            'cor_primaria' => '#000000',
            'cor_sucesso' => '#111111',
            'cor_alerta' => '#222222',
            'cor_erro' => '#333333',
        ]);

        Livewire::test(EditIdentidadeVisual::class, ['record' => $registro->id])
            ->callAction('restaurar_padroes')
            ->assertSet('data.cor_primaria', IdentidadeVisual::defaults()['cor_primaria'])
            ->assertSet('data.cor_sucesso', IdentidadeVisual::defaults()['cor_sucesso']);

        $this->assertDatabaseHas('identidade_visual', [
            'nome_sistema' => 'Nome customizado',
            'cor_primaria' => '#000000',
        ]);
    }

    public function test_master_page_shows_preview_and_history(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs(User::factory()->platformAdmin()->create());

        $registro = IdentidadeVisual::obterOuCriar();

        Livewire::test(EditIdentidadeVisual::class, ['record' => $registro->id])
            ->assertOk()
            ->assertSee('Pré-visualização do painel')
            ->assertSee('Histórico de alterações')
            ->assertSee('Marca do painel master');
    }

    public function test_tenant_resource_renders_and_persists_branding(): void
    {
        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $tenant = Tenant::create(['te_name' => 'Loja Original']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();

        Livewire::test(EditTenantBranding::class, ['record' => $tenant->id])
            ->assertOk()
            ->assertSee('Identidade Visual da Loja')
            ->fillForm([
                'nome_loja' => 'Minha Marca',
                'cor_primaria_light' => '#7C3AED',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas('tenant_themes', [
            'tenant_id' => $tenant->id,
            'nome_loja' => 'Minha Marca',
            'cor_primaria_light' => '#7C3AED',
        ]);

        Cache::forget("branding.tenant.{$tenant->id}");
        $branding = app(BrandingService::class);
        $this->assertSame('Minha Marca', $branding->nomeTenant($tenant));
    }

    public function test_tenant_resource_is_hidden_for_non_managers(): void
    {
        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $tenant = Tenant::create(['te_name' => 'Loja Vendedor']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();

        $this->assertFalse(TenantBrandingResource::canAccess());
    }

    public function test_tenant_restore_defaults_updates_form_without_saving(): void
    {
        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $tenant = Tenant::create(['te_name' => 'Loja Cores']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
        ]);

        $theme = TenantTheme::obterOuCriarParaTenant($tenant->id);
        $theme->update([
            'nome_loja' => 'Marca Protegida',
            'cor_primaria_light' => '#000000',
            'cor_sucesso_light' => '#111111',
        ]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();

        Livewire::test(EditTenantBranding::class, ['record' => $theme->id])
            ->callAction('restaurar_padroes')
            ->assertSet('data.cor_primaria_light', TenantTheme::defaults()['cor_primaria_light'])
            ->assertSet('data.nome_loja', 'Marca Protegida');

        $this->assertDatabaseHas('tenant_themes', [
            'tenant_id' => $tenant->id,
            'nome_loja' => 'Marca Protegida',
            'cor_primaria_light' => '#000000',
        ]);
    }

    public function test_tenant_page_shows_preview_and_history(): void
    {
        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $tenant = Tenant::create(['te_name' => 'Loja Preview']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, [
            'tu_papel' => TenantPortalRole::Leader->value,
        ]);

        $theme = TenantTheme::obterOuCriarParaTenant($tenant->id);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();

        Livewire::test(EditTenantBranding::class, ['record' => $theme->id])
            ->assertOk()
            ->assertSee('Pré-visualização do painel')
            ->assertSee('Histórico de alterações')
            ->assertSee('Marca da loja');
    }
}
