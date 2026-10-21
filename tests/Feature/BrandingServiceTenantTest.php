<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantTheme;
use App\Services\BrandingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BrandingServiceTenantTest extends TestCase
{
    use RefreshDatabase;

    private BrandingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BrandingService::class);
    }

    public function test_cores_filament_tenant_uses_cor_primaria_light(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Cores']);
        TenantTheme::obterOuCriarParaTenant($tenant->id)->update(['cor_primaria_light' => '#7C3AED']);

        $cores = $this->service->coresFilamentTenant($tenant);

        $this->assertArrayHasKey('primary', $cores);
        $this->assertIsArray($cores['primary']);
        $this->assertArrayHasKey(500, $cores['primary']);
    }

    public function test_gerar_css_variaveis_tenant_has_root_block(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja CSS']);
        TenantTheme::obterOuCriarParaTenant($tenant->id)->update(['cor_primaria_light' => '#10B981']);

        $css = $this->service->gerarCssVariaveisTenant($tenant);

        $this->assertStringContainsString(':root', $css);
        $this->assertStringContainsString('--brand-primary', $css);
        $this->assertStringContainsString('#10B981', $css);
    }

    public function test_gerar_css_variaveis_tenant_has_dark_block(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Dark']);
        TenantTheme::obterOuCriarParaTenant($tenant->id)->update(['cor_primaria_dark' => '#A78BFA']);

        $css = $this->service->gerarCssVariaveisTenant($tenant);

        $this->assertStringContainsString('.dark', $css);
        $this->assertStringContainsString('#A78BFA', $css);
        $this->assertStringContainsString('--color-primary-500', $css);
    }

    public function test_gerar_css_variaveis_tenant_omits_null_backgrounds(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja No BG']);
        TenantTheme::obterOuCriarParaTenant($tenant->id);

        $css = $this->service->gerarCssVariaveisTenant($tenant);

        $this->assertStringNotContainsString('--brand-bg-page', $css);
    }

    public function test_gerar_css_variaveis_tenant_includes_backgrounds_when_set(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja BG']);
        TenantTheme::obterOuCriarParaTenant($tenant->id)->update([
            'bg_page_light' => '#F9FAFB',
            'bg_page_dark' => '#111827',
        ]);

        $css = $this->service->gerarCssVariaveisTenant($tenant);

        $this->assertStringContainsString('--brand-bg-page', $css);
        $this->assertStringContainsString('#F9FAFB', $css);
        $this->assertStringContainsString('#111827', $css);
    }

    public function test_nome_tenant_uses_nome_loja(): void
    {
        $tenant = Tenant::create(['te_name' => 'Nome Original']);
        TenantTheme::obterOuCriarParaTenant($tenant->id)->update(['nome_loja' => 'Marca Custom']);

        $nome = $this->service->nomeTenant($tenant);

        $this->assertSame('Marca Custom', $nome);
    }

    public function test_light_only_background_does_not_leak_into_dark_mode(): void
    {
        $tenant = Tenant::factory()->create();
        TenantTheme::obterOuCriarParaTenant($tenant->id)->update(['bg_page_light' => '#FFFFFF']);

        $css = $this->service->gerarCssVariaveisTenant($tenant);

        $this->assertStringContainsString('--brand-bg-page: #FFFFFF;', $css);
        $this->assertStringContainsString('--brand-bg-page: var(--panel-canvas, #f6f5f0);', $css);
    }

    public function test_nome_tenant_falls_back_to_te_name_when_no_nome_loja(): void
    {
        $tenant = Tenant::create(['te_name' => 'Fallback Shop']);
        TenantTheme::obterOuCriarParaTenant($tenant->id);

        $nome = $this->service->nomeTenant($tenant);

        $this->assertSame('Fallback Shop', $nome);
    }

    public function test_invalidar_cache_tenant_removes_cached_data(): void
    {
        $tenant = Tenant::create(['te_name' => 'Cache Test']);
        TenantTheme::obterOuCriarParaTenant($tenant->id)->update(['nome_loja' => 'V1']);

        $this->assertSame('V1', $this->service->nomeTenant($tenant));

        TenantTheme::where('tenant_id', $tenant->id)->update(['nome_loja' => 'V2']);

        // Cache still returns V1 before invalidation
        $this->assertSame('V1', $this->service->nomeTenant($tenant));

        $this->service->invalidarCacheTenant($tenant->id);

        $this->assertSame('V2', $this->service->nomeTenant($tenant));
    }
}
