<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\TenantPortalRole;
use App\Filament\App\Widgets\DashboardOverviewWidget;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardOverviewWidgetTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $this->tenant = Tenant::create(['te_name' => 'Loja Teste Dashboard']);

        $user = User::factory()->create();
        $this->tenant->users()->attach($user, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
        ]);

        $this->actingAs($user);

        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
        Filament::bootCurrentPanel();
    }

    /** @return array<Stat> */
    private function stats(): array
    {
        $component = Livewire::test(DashboardOverviewWidget::class)->instance();

        $method = new \ReflectionMethod($component, 'getStats');
        $method->setAccessible(true);

        return $method->invoke($component);
    }

    public function test_vendas_hoje_soma_apenas_pedidos_pagos_do_dia(): void
    {
        // Pedidos pagos hoje — devem entrar no faturamento
        Order::factory()->create([
            'status' => OrderStatus::Paid,
            'total_amount' => 100.00,
            'created_at' => Carbon::today(),
        ]);
        Order::factory()->create([
            'status' => OrderStatus::PartiallyRefunded,
            'total_amount' => 50.00,
            'created_at' => Carbon::today(),
        ]);

        // Pedido pago de ontem — não deve entrar
        Order::factory()->create([
            'status' => OrderStatus::Paid,
            'total_amount' => 999.99,
            'created_at' => Carbon::yesterday(),
        ]);

        // Pedido pendente hoje — não deve entrar
        Order::factory()->create([
            'status' => OrderStatus::Pending,
            'total_amount' => 30.00,
            'created_at' => Carbon::today(),
        ]);

        $vendaStat = $this->stats()[0];

        // R$ 100,00 + R$ 50,00 = R$ 150,00
        $this->assertStringContainsString('150,00', (string) $vendaStat->getValue());
    }

    public function test_pedidos_pendentes_conta_todos_independente_de_data(): void
    {
        Order::factory()->create(['status' => OrderStatus::Pending, 'created_at' => Carbon::today()]);
        Order::factory()->create(['status' => OrderStatus::Pending, 'created_at' => Carbon::yesterday()]);
        Order::factory()->create(['status' => OrderStatus::Pending, 'created_at' => Carbon::now()->subDays(30)]);

        // Não-pendentes não devem contar
        Order::factory()->create(['status' => OrderStatus::Paid]);
        Order::factory()->create(['status' => OrderStatus::Canceled]);

        $pendingStat = $this->stats()[1];

        $this->assertSame('3', (string) $pendingStat->getValue());
    }

    public function test_produtos_ativos_conta_apenas_com_status_active(): void
    {
        Product::factory()->count(4)->create(['status' => 'active']);
        Product::factory()->count(2)->create(['status' => 'inactive']);

        $productStat = $this->stats()[2];

        $this->assertSame('4', (string) $productStat->getValue());
    }

    public function test_novos_clientes_mes_conta_apenas_do_mes_corrente(): void
    {
        Customer::factory()->count(3)->create(['created_at' => Carbon::now()]);
        Customer::factory()->count(5)->create(['created_at' => Carbon::now()->subMonth()]);

        $customerStat = $this->stats()[3];

        $this->assertSame('3', (string) $customerStat->getValue());
    }

    public function test_novos_clientes_mostra_delta_positivo_vs_mes_anterior(): void
    {
        Customer::factory()->count(5)->create(['created_at' => Carbon::now()]);
        Customer::factory()->count(2)->create(['created_at' => Carbon::now()->subMonth()]);

        $customerStat = $this->stats()[3];

        // Delta = +3 vs. mês anterior
        $this->assertStringContainsString('+3', (string) $customerStat->getDescription());
    }

    public function test_novos_clientes_mostra_delta_negativo_vs_mes_anterior(): void
    {
        Customer::factory()->count(1)->create(['created_at' => Carbon::now()]);
        Customer::factory()->count(4)->create(['created_at' => Carbon::now()->subMonth()]);

        $customerStat = $this->stats()[3];

        $this->assertStringContainsString('-3', (string) $customerStat->getDescription());
    }

    public function test_vendas_hoje_retorna_zero_quando_sem_pedidos(): void
    {
        $vendaStat = $this->stats()[0];

        $this->assertStringContainsString('0,00', (string) $vendaStat->getValue());
    }

    public function test_cor_pedidos_pendentes_e_warning_quando_ha_pendentes(): void
    {
        Order::factory()->create(['status' => OrderStatus::Pending]);

        $pendingStat = $this->stats()[1];

        $this->assertSame('warning', $pendingStat->getColor());
    }

    public function test_cor_pedidos_pendentes_e_success_quando_nao_ha_pendentes(): void
    {
        Order::factory()->create(['status' => OrderStatus::Paid]);

        $pendingStat = $this->stats()[1];

        $this->assertSame('success', $pendingStat->getColor());
    }
}
