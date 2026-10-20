<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BillingPeriod;
use App\Enums\OrderStatus;
use App\Enums\TenantPortalRole;
use App\Filament\Admin\Widgets\PlatformAgendaWidget;
use App\Filament\Admin\Widgets\PlatformAttentionWidget;
use App\Filament\Admin\Widgets\PlatformOverviewWidget;
use App\Filament\Admin\Widgets\StoresOverviewWidget;
use App\Filament\Admin\Widgets\SubscriptionBreakdownWidget;
use App\Filament\App\Pages\Dashboard;
use App\Filament\App\Resources\Orders\Pages\ListOrders;
use App\Filament\App\Widgets\BestSellingProductsWidget;
use App\Filament\App\Widgets\RestockProductsWidget;
use App\Filament\App\Widgets\StoreAttentionWidget;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PanelDashboardService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PanelDashboardExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_forecast_is_monthly_and_uses_only_current_subscriptions(): void
    {
        $tenant = Tenant::factory()->create(['te_ativo' => true]);
        $plan = Plan::factory()->create(['price' => '1200.00', 'billing_period' => BillingPeriod::Yearly]);
        Subscription::factory()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);
        Subscription::factory()->create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id]);
        $disabled = Tenant::factory()->create(['te_ativo' => false]);
        Subscription::factory()->create(['tenant_id' => $disabled->id, 'plan_id' => $plan->id]);
        Subscription::factory()->expired()->create(['plan_id' => $plan->id]);
        $service = app(PanelDashboardService::class);
        $summary = $service->platformSummary();

        $this->assertSame(1, $summary['stores']);
        $this->assertSame(10000, $summary['forecastCents']);
        $this->assertSame('R$ 100,00', $service->formatCents($summary['forecastCents']));
        $this->assertSame(3, array_sum($summary['statuses']));
    }

    public function test_admin_dashboard_renders_new_widgets_with_real_data(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs(User::factory()->platformAdmin()->create(), 'admin');
        Subscription::factory()->pastDue()->create();

        Livewire::test(PlatformOverviewWidget::class)->assertSee('Receita recebida')->assertSee('Recebimentos ainda não registrados');
        Livewire::test(PlatformAttentionWidget::class)->assertSee('1 assinatura precisa de atenção');
        foreach ([StoresOverviewWidget::class, PlatformAgendaWidget::class, SubscriptionBreakdownWidget::class] as $widget) {
            Livewire::test($widget)->assertOk();
        }
        $this->get('/admin')->assertOk()->assertSee('Sua plataforma, em perspectiva.');
    }

    public function test_restock_cards_include_zero_stock_and_low_variations_but_exclude_inactive_products(): void
    {
        $this->bootStore();
        $zero = Product::factory()->create(['stock' => 0, 'low_stock_threshold' => 0]);
        $variable = Product::factory()->create(['has_variations' => true, 'stock' => 0]);
        ProductVariation::factory()->create(['product_id' => $variable->id, 'stock' => 2, 'low_stock_threshold' => 3]);
        ProductVariation::factory()->create(['product_id' => $variable->id, 'stock' => 50, 'low_stock_threshold' => 3]);
        $inactive = Product::factory()->create(['status' => 'inactive', 'stock' => 0]);
        $healthy = Product::factory()->create(['stock' => 20]);

        $this->assertSame(2, app(PanelDashboardService::class)->restockQuery()->count());
        Livewire::test(RestockProductsWidget::class)->assertCanSeeTableRecords([$zero, $variable])->assertCanNotSeeTableRecords([$inactive, $healthy]);
        Livewire::test(StoreAttentionWidget::class)->assertSee('2 produtos precisam de reposição');
    }

    public function test_store_dashboard_handles_empty_stock_and_sales(): void
    {
        $this->bootStore();
        Livewire::test(RestockProductsWidget::class)->assertSee('Estoque em dia');
        Livewire::test(BestSellingProductsWidget::class)->assertSee('As próximas vendas aparecem aqui');
        Livewire::test(StoreAttentionWidget::class)
            ->assertSee('Todos os pedidos em dia')
            ->assertSee('tab=pending', escape: false);
        Livewire::test(Dashboard::class)->assertOk();
    }

    public function test_pending_orders_shortcut_selects_only_pending_records(): void
    {
        $this->bootStore();
        $pending = Order::factory()->create(['status' => OrderStatus::Pending]);
        $paid = Order::factory()->create(['status' => OrderStatus::Paid]);

        Livewire::withQueryParams(['tab' => 'pending'])
            ->test(ListOrders::class)
            ->assertSet('activeTab', 'pending')
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$paid]);
    }

    private function bootStore(): void
    {
        $this->artisan('migrate', ['--path' => 'database/migrations/tenant']);
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);
        $this->actingAs($user, 'web');
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();
    }
}
