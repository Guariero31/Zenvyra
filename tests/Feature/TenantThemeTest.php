<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_obter_ou_criar_para_tenant_creates_with_defaults(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Teste']);

        $theme = TenantTheme::obterOuCriarParaTenant($tenant->id);

        $this->assertInstanceOf(TenantTheme::class, $theme);
        $this->assertTrue($theme->exists);
        $this->assertSame($tenant->id, $theme->tenant_id);
        $this->assertSame('#4F46E5', $theme->cor_primaria_light);
        $this->assertSame('#818CF8', $theme->cor_primaria_dark);
        $this->assertSame('#10B981', $theme->cor_sucesso_light);
        $this->assertNull($theme->nome_loja);
        $this->assertNull($theme->bg_page_light);
    }

    public function test_obter_ou_criar_returns_existing_on_second_call(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Existente']);

        $first = TenantTheme::obterOuCriarParaTenant($tenant->id);
        $second = TenantTheme::obterOuCriarParaTenant($tenant->id);

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('tenant_themes', 1);
    }

    public function test_saves_light_and_dark_fields(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Cores']);
        $theme = TenantTheme::obterOuCriarParaTenant($tenant->id);

        $theme->update([
            'nome_loja' => 'Marca Legal',
            'cor_primaria_light' => '#7C3AED',
            'cor_primaria_dark' => '#A78BFA',
            'bg_page_light' => '#F9FAFB',
            'bg_page_dark' => '#111827',
        ]);

        $this->assertDatabaseHas('tenant_themes', [
            'tenant_id' => $tenant->id,
            'nome_loja' => 'Marca Legal',
            'cor_primaria_light' => '#7C3AED',
            'cor_primaria_dark' => '#A78BFA',
            'bg_page_light' => '#F9FAFB',
            'bg_page_dark' => '#111827',
        ]);
    }

    public function test_tenant_has_theme_relationship(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Rel']);
        TenantTheme::obterOuCriarParaTenant($tenant->id);

        $tenant->refresh();
        $this->assertInstanceOf(TenantTheme::class, $tenant->theme);
        $this->assertSame($tenant->id, $tenant->theme->tenant_id);
    }

    public function test_theme_is_deleted_when_tenant_is_deleted(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Cascade']);
        TenantTheme::obterOuCriarParaTenant($tenant->id);

        $this->assertDatabaseCount('tenant_themes', 1);

        $tenant->delete();

        $this->assertDatabaseCount('tenant_themes', 0);
    }
}
