<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\App\Pages\SettingsPage;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantGestaoSettings;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['te_name' => 'Loja Config']);

        $this->admin = User::factory()->create();
        $this->tenant->users()->attach($this->admin, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
        ]);

        $this->member = User::factory()->create();
        $this->tenant->users()->attach($this->member, [
            'tu_papel' => TenantPortalRole::Member->value,
        ]);
    }

    private function asAdmin(): void
    {
        $this->actingAs($this->admin);
        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
        Filament::bootCurrentPanel();
    }

    private function asMember(): void
    {
        $this->actingAs($this->member);
        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
        Filament::bootCurrentPanel();
    }

    public function test_settings_page_renders_for_admin(): void
    {
        $this->asAdmin();

        Livewire::test(SettingsPage::class)->assertOk();
    }

    public function test_settings_page_is_blocked_for_member(): void
    {
        $this->asMember();

        $this->assertFalse(SettingsPage::canAccess());
    }

    public function test_settings_page_loads_current_settings(): void
    {
        $this->tenant->update([
            'te_gestao_settings' => TenantGestaoSettings::from([
                'exige_telefone_cliente' => true,
                'exige_email_cliente' => false,
                'modulo_vendedores_ativo' => false,
                'permitir_estoque_negativo' => true,
            ])->toArray(),
        ]);

        $this->asAdmin();

        Livewire::test(SettingsPage::class)
            ->assertSet('data.exige_telefone_cliente', true)
            ->assertSet('data.exige_email_cliente', false)
            ->assertSet('data.modulo_vendedores_ativo', false)
            ->assertSet('data.permitir_estoque_negativo', true);
    }

    public function test_save_persists_settings_to_tenant(): void
    {
        $this->asAdmin();

        Livewire::test(SettingsPage::class)
            ->set('data.exige_telefone_cliente', true)
            ->set('data.exige_email_cliente', true)
            ->set('data.modulo_vendedores_ativo', false)
            ->set('data.permitir_estoque_negativo', false)
            ->callAction('save');

        $this->tenant->refresh();
        $settings = $this->tenant->gestaoSettings();

        $this->assertTrue($settings->exigeTelefoneCliente());
        $this->assertTrue($settings->exigeEmailCliente());
        $this->assertFalse($settings->moduloVendedoresAtivo());
        $this->assertFalse($settings->permitirEstoqueNegativo());
    }

    public function test_save_sends_success_notification(): void
    {
        $this->asAdmin();

        Livewire::test(SettingsPage::class)
            ->callAction('save')
            ->assertNotified('Configurações salvas');
    }

    public function test_leader_can_access_settings_page(): void
    {
        $leader = User::factory()->create();
        $this->tenant->users()->attach($leader, [
            'tu_papel' => TenantPortalRole::Leader->value,
        ]);

        $this->actingAs($leader);
        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);

        $this->assertTrue(SettingsPage::canAccess());
    }

    public function test_vendedor_cannot_access_settings_page(): void
    {
        $seller = User::factory()->create();
        $this->tenant->users()->attach($seller, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
        ]);

        $this->actingAs($seller);
        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);

        $this->assertFalse(SettingsPage::canAccess());
    }
}
