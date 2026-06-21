<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\Customers\Pages\CreateCustomer;
use App\Filament\App\Resources\TeamMembers\TeamMemberResource;
use App\Filament\App\Widgets\SellersPerformanceChart;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\StockManager;
use App\Support\TenantGestaoSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class TenantGestaoSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_are_applied_when_settings_are_null(): void
    {
        $tenant = Tenant::factory()->create(['te_gestao_settings' => null]);
        $settings = $tenant->gestaoSettings();

        $this->assertFalse($settings->exigeTelefoneCliente());
        $this->assertFalse($settings->exigeEmailCliente());
        $this->assertTrue($settings->moduloVendedoresAtivo());
        $this->assertFalse($settings->permitirEstoqueNegativo());
    }

    public function test_customer_form_requires_phone_when_setting_is_enabled(): void
    {
        $this->artisan('migrate', ['--path' => 'database/migrations/tenant']);

        $tenant = Tenant::factory()->create([
            'te_gestao_settings' => TenantGestaoSettings::from([
                'exige_telefone_cliente' => true,
            ])->toArray(),
        ]);

        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();

        session(['tenant_id' => $tenant->getKey()]);

        Livewire::test(CreateCustomer::class)
            ->fillForm([
                'name' => 'Cliente Sem Telefone',
                'phone' => null,
                'email' => 'cliente@teste.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['phone' => 'required']);
    }

    public function test_commission_features_are_hidden_when_module_is_disabled(): void
    {
        $tenant = Tenant::factory()->create([
            'te_gestao_settings' => TenantGestaoSettings::from([
                'modulo_vendedores_ativo' => false,
            ])->toArray(),
        ]);

        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);

        $this->assertFalse(SellersPerformanceChart::canView());
        $this->assertTrue(TeamMemberResource::canViewAny());
    }

    public function test_stock_manager_blocks_negative_stock_when_setting_is_disabled(): void
    {
        $this->artisan('migrate', ['--path' => 'database/migrations/tenant']);

        $tenant = Tenant::factory()->create([
            'te_gestao_settings' => TenantGestaoSettings::from([
                'permitir_estoque_negativo' => false,
            ])->toArray(),
        ]);

        session(['tenant_id' => $tenant->getKey()]);

        $product = Product::create([
            'title' => 'Produto Estoque',
            'sku' => 'SKU-001',
            'price' => 10,
            'stock' => 1,
        ]);

        $this->expectException(ValidationException::class);

        app(StockManager::class)->adjustment(
            product: $product,
            variation: null,
            signedQuantity: -5,
            reason: 'Teste',
            userId: null,
        );
    }
}
