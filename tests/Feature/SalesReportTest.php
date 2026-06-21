<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\TenantPortalRole;
use App\Filament\App\Widgets\MonthlyRevenueChart;
use App\Filament\App\Widgets\MonthlySalesCountChart;
use App\Filament\App\Widgets\PaymentMethodRevenueChart;
use App\Filament\App\Widgets\SalesStatsOverview;
use App\Filament\App\Widgets\SellersPerformanceChart;
use App\Filament\App\Widgets\TopProductsChart;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SalesReportService;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    private SalesReportService $reports;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $this->reports = app(SalesReportService::class);
    }

    public function test_current_month_kpis_only_count_sold_orders(): void
    {
        $product = $this->createProduct();

        $this->paidOrder($product, quantity: 2, price: 100);
        $this->paidOrder($product, quantity: 1, price: 50);
        $this->pendingOrder($product, quantity: 5, price: 100);

        $kpis = $this->reports->currentMonthKpis();

        $this->assertSame(250.0, $kpis['gross']);
        $this->assertSame(0.0, $kpis['refunds']);
        $this->assertSame(250.0, $kpis['net']);
        $this->assertSame(2, $kpis['salesCount']);
        $this->assertSame(125.0, $kpis['averageTicket']);
    }

    public function test_refunds_reduce_net_revenue(): void
    {
        $product = $this->createProduct();
        $order = $this->paidOrder($product, quantity: 2, price: 100);
        $order->forceFill([
            'refunded_amount' => 40,
            'status' => OrderStatus::PartiallyRefunded,
        ])->save();

        $kpis = $this->reports->currentMonthKpis();

        $this->assertSame(200.0, $kpis['gross']);
        $this->assertSame(40.0, $kpis['refunds']);
        $this->assertSame(160.0, $kpis['net']);
        $this->assertSame(1, $kpis['salesCount']);
    }

    public function test_monthly_series_buckets_current_and_past_months(): void
    {
        $product = $this->createProduct();

        $this->paidOrder($product, quantity: 1, price: 100, when: CarbonImmutable::now());
        $this->paidOrder($product, quantity: 1, price: 70, when: CarbonImmutable::now()->subMonths(2));

        $series = $this->reports->monthlySeries(12);

        $this->assertCount(12, $series['labels']);
        $this->assertCount(12, $series['net']);
        $this->assertSame(100.0, $series['net'][11]);
        $this->assertSame(70.0, $series['net'][9]);
        $this->assertSame(1, $series['count'][11]);
    }

    public function test_revenue_by_payment_method_groups_totals(): void
    {
        $product = $this->createProduct();

        $this->paidOrder($product, quantity: 1, price: 100, payment: 'pix');
        $this->paidOrder($product, quantity: 1, price: 50, payment: 'pix');
        $this->paidOrder($product, quantity: 1, price: 30, payment: 'dinheiro');

        $data = $this->reports->revenueByPaymentMethod();

        $combined = array_combine($data['labels'], $data['data']);

        $this->assertSame(150.0, $combined['Pix']);
        $this->assertSame(30.0, $combined['Dinheiro']);
    }

    public function test_top_products_by_revenue_orders_by_total(): void
    {
        $cheap = $this->createProduct('Camiseta');
        $expensive = $this->createProduct('Tênis');

        $this->paidOrder($cheap, quantity: 1, price: 50);
        $this->paidOrder($expensive, quantity: 2, price: 300);

        $data = $this->reports->topProductsByRevenue();

        $this->assertSame('Tênis', $data['labels'][0]);
        $this->assertSame(600.0, $data['data'][0]);
    }

    public function test_sellers_performance_groups_revenue_and_commission(): void
    {
        $sellerUser = User::factory()->create(['name' => 'Maria Vendas']);
        $tenant = Tenant::create(['te_name' => 'Loja Ranking']);
        $tenant->users()->attach($sellerUser, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
            'tu_commission_rate' => 10,
        ]);

        $this->actingAs($sellerUser);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();

        $product = $this->createProduct('Produto Comissionado');
        $order = Order::factory()->create([
            'user_id' => $sellerUser->id,
            'status' => OrderStatus::Paid,
        ]);
        OrderItem::factory()->for($order)->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price_at_sale' => 100,
            'commission_rate' => 10,
            'commission_amount' => 20,
        ]);
        $order->update(['total_amount' => 200, 'commission_amount' => 20]);

        $data = $this->reports->sellersPerformance();

        $this->assertSame('Maria Vendas', $data['labels'][0]);
        $this->assertSame(200.0, $data['gross'][0]);
        $this->assertSame(20.0, $data['commission'][0]);
        $this->assertSame(1, $data['salesCount'][0]);
    }

    public function test_sellers_performance_excludes_non_vendedor_users(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Filtro']);
        $admin = User::factory()->create(['name' => 'Admin Loja']);
        $seller = User::factory()->create(['name' => 'Vendedor Real']);

        $tenant->users()->attach($admin, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
        ]);
        $tenant->users()->attach($seller, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
        ]);

        $product = $this->createProduct('Produto');

        $adminOrder = Order::factory()->create([
            'user_id' => $admin->id,
            'status' => OrderStatus::Paid,
            'total_amount' => 500,
            'commission_amount' => 50,
        ]);
        OrderItem::factory()->for($adminOrder)->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_sale' => 500,
        ]);

        $sellerOrder = Order::factory()->create([
            'user_id' => $seller->id,
            'status' => OrderStatus::Paid,
        ]);
        OrderItem::factory()->for($sellerOrder)->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_sale' => 100,
        ]);
        $sellerOrder->update(['total_amount' => 100, 'commission_amount' => 10]);

        $data = $this->reports->sellersPerformance(tenant: $tenant);

        $this->assertSame(['Vendedor Real'], $data['labels']);
        $this->assertSame([100.0], $data['gross']);
        $this->assertSame([10.0], $data['commission']);
    }

    public function test_sellers_performance_lists_all_vendedores_even_without_sales(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Equipe']);
        $withSales = User::factory()->create(['name' => 'Com Vendas']);
        $withoutSales = User::factory()->create(['name' => 'Sem Vendas']);

        $tenant->users()->attach($withSales, ['tu_papel' => TenantPortalRole::Vendedor->value]);
        $tenant->users()->attach($withoutSales, ['tu_papel' => TenantPortalRole::Vendedor->value]);

        $product = $this->createProduct('Produto');

        $order = Order::factory()->create([
            'user_id' => $withSales->id,
            'status' => OrderStatus::Paid,
        ]);
        OrderItem::factory()->for($order)->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_sale' => 150,
        ]);
        $order->update(['total_amount' => 150, 'commission_amount' => 15]);

        $data = $this->reports->sellersPerformance(tenant: $tenant);

        $this->assertSame(['Com Vendas', 'Sem Vendas'], $data['labels']);
        $this->assertSame([150.0, 0.0], $data['gross']);
        $this->assertSame([15.0, 0.0], $data['commission']);
    }

    public function test_dashboard_widgets_render_for_tenant(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja BI']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();

        $product = $this->createProduct();
        $this->paidOrder($product, quantity: 1, price: 120);

        Livewire::test(SalesStatsOverview::class)->assertOk();
        Livewire::test(MonthlyRevenueChart::class)->assertOk();
        Livewire::test(MonthlySalesCountChart::class)->assertOk();
        Livewire::test(PaymentMethodRevenueChart::class)->assertOk();
        Livewire::test(TopProductsChart::class)->assertOk();
        Livewire::test(SellersPerformanceChart::class)->assertOk();
    }

    private function createProduct(string $title = 'Produto Demo'): Product
    {
        $category = Category::create([
            'name' => 'Geral',
            'slug' => 'geral-'.uniqid(),
        ]);

        return Product::create([
            'category_id' => $category->id,
            'title' => $title,
            'sku' => 'SKU-'.uniqid(),
            'price' => 100,
            'cost_price' => 50,
            'stock' => 100,
            'low_stock_threshold' => 5,
            'status' => 'active',
        ]);
    }

    private function paidOrder(Product $product, int $quantity, float $price, ?CarbonImmutable $when = null, string $payment = 'pix'): Order
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::Paid,
            'payment_method' => $payment,
            'created_at' => $when ?? CarbonImmutable::now(),
        ]);

        OrderItem::factory()->for($order)->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price_at_sale' => $price,
        ]);

        return $order->refresh();
    }

    private function pendingOrder(Product $product, int $quantity, float $price): Order
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::Pending,
            'created_at' => CarbonImmutable::now(),
        ]);

        OrderItem::factory()->for($order)->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price_at_sale' => $price,
        ]);

        return $order->refresh();
    }
}
