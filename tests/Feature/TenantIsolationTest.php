<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderOrigin;
use App\Enums\OrderStatus;
use App\Enums\TenantPortalRole;
use App\Models\Order;
use App\Models\Scopes\TenantMemberOperatorScope;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        Filament::setCurrentPanel('app');
    }

    public function test_order_scope_hides_operators_from_other_tenants(): void
    {
        $user = User::factory()->create();

        $tenant = Tenant::create([
            'te_name' => 'Loja A',
            'te_slug' => 'loja-a',
            'te_schema' => 'tenant_loja_a',
        ]);

        $outroTenant = Tenant::create([
            'te_name' => 'Loja B',
            'te_slug' => 'loja-b',
            'te_schema' => 'tenant_loja_b',
        ]);

        $vendedorLocal = User::factory()->create(['name' => 'Vendedor Local']);
        $vendedorExterno = User::factory()->create(['name' => 'Vendedor Externo']);

        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);
        $tenant->users()->attach($vendedorLocal, ['tu_papel' => TenantPortalRole::Vendedor->value]);
        $outroTenant->users()->attach($vendedorExterno, ['tu_papel' => TenantPortalRole::Vendedor->value]);

        Order::withoutGlobalScope(TenantMemberOperatorScope::class)->create([
            'user_id' => $vendedorLocal->id,
            'origin' => OrderOrigin::Balcao,
            'total_amount' => 100,
            'commission_amount' => 0,
            'status' => OrderStatus::Pending,
        ]);

        Order::withoutGlobalScope(TenantMemberOperatorScope::class)->create([
            'user_id' => $vendedorExterno->id,
            'origin' => OrderOrigin::Balcao,
            'total_amount' => 200,
            'commission_amount' => 0,
            'status' => OrderStatus::Pending,
        ]);

        $this->actingAs($user);
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();

        $this->assertSame(1, Order::query()->count());
        $this->assertSame('Vendedor Local', Order::query()->first()?->operator?->name);
    }

    public function test_tenant_context_resolves_from_filament_not_session(): void
    {
        $user = User::factory()->create();

        $tenantAtual = Tenant::create([
            'te_name' => 'Loja Atual',
            'te_slug' => 'loja-atual',
            'te_schema' => 'tenant_loja_atual',
        ]);

        $tenantSessao = Tenant::create([
            'te_name' => 'Loja Sessao',
            'te_slug' => 'loja-sessao',
            'te_schema' => 'tenant_loja_sessao',
        ]);

        $tenantAtual->users()->attach($user, ['tu_papel' => TenantPortalRole::Member->value]);

        $this->actingAs($user);
        Filament::setTenant($tenantAtual);
        Filament::bootCurrentPanel();

        session([
            'tenant_id' => $tenantSessao->getKey(),
            'tenant_schema' => $tenantSessao->te_schema,
        ]);

        $this->assertSame($tenantAtual->getKey(), TenantContext::current()?->getKey());
        $this->assertSame('tenant_loja_atual', TenantContext::current()?->te_schema);
    }

    public function test_apply_search_path_uses_filament_tenant_schema_on_pgsql(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requer PostgreSQL para validar o search_path.');
        }

        $user = User::factory()->create();

        $tenantAtual = Tenant::create([
            'te_name' => 'Loja A',
            'te_slug' => 'loja-a',
            'te_schema' => 'tenant_loja_a',
        ]);

        $tenantSessao = Tenant::create([
            'te_name' => 'Loja B',
            'te_slug' => 'loja-b',
            'te_schema' => 'tenant_loja_b',
        ]);

        $tenantAtual->users()->attach($user, ['tu_papel' => TenantPortalRole::Member->value]);

        $this->actingAs($user);
        Filament::setTenant($tenantAtual);
        Filament::bootCurrentPanel();

        session(['tenant_schema' => $tenantSessao->te_schema]);

        TenantContext::applySearchPath();

        $searchPath = DB::selectOne('SHOW search_path');

        $this->assertStringContainsString('tenant_loja_a', (string) $searchPath->search_path);
        $this->assertStringNotContainsString('tenant_loja_b', (string) $searchPath->search_path);
    }
}
