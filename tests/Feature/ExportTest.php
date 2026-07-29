<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\Customers\Pages\ListCustomers;
use App\Filament\App\Resources\Orders\Pages\ListOrders;
use App\Filament\App\Resources\Products\Pages\ListProducts;
use App\Filament\Exports\CustomerExporter;
use App\Filament\Exports\OrderExporter;
use App\Filament\Exports\ProductExporter;
use App\Models\Tenant;
use App\Models\User;
use Filament\Actions\ExportAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $this->tenant = Tenant::factory()->create();
        $this->admin  = User::factory()->create();
        $this->tenant->users()->attach($this->admin, [
            'tu_papel'          => TenantPortalRole::AdministradorCliente->value,
            'tu_commission_rate' => 0,
        ]);

        $this->actingAs($this->admin);
        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
    }

    // ── Colunas dos Exporters ─────────────────────────────────────────────────

    public function test_order_exporter_has_expected_columns(): void
    {
        $columns = collect(OrderExporter::getColumns())
            ->map(fn ($col) => $col->getName())
            ->all();

        $this->assertContains('id', $columns);
        $this->assertContains('status', $columns);
        $this->assertContains('total_amount', $columns);
        $this->assertContains('customer.name', $columns);
        $this->assertContains('payment_method', $columns);
    }

    public function test_customer_exporter_has_expected_columns(): void
    {
        $columns = collect(CustomerExporter::getColumns())
            ->map(fn ($col) => $col->getName())
            ->all();

        $this->assertContains('name', $columns);
        $this->assertContains('email', $columns);
        $this->assertContains('phone', $columns);
        $this->assertContains('orders_count', $columns);
    }

    public function test_product_exporter_has_expected_columns(): void
    {
        $columns = collect(ProductExporter::getColumns())
            ->map(fn ($col) => $col->getName())
            ->all();

        $this->assertContains('title', $columns);
        $this->assertContains('sku', $columns);
        $this->assertContains('price', $columns);
        $this->assertContains('stock', $columns);
        $this->assertContains('status', $columns);
    }

    // ── ExportAction presente nas páginas ─────────────────────────────────────

    public function test_orders_list_page_has_export_action(): void
    {
        Livewire::test(ListOrders::class, ['tenant' => $this->tenant])
            ->assertActionExists('export');
    }

    public function test_customers_list_page_has_export_action(): void
    {
        Livewire::test(ListCustomers::class, ['tenant' => $this->tenant])
            ->assertActionExists('export');
    }

    public function test_products_list_page_has_export_action(): void
    {
        Livewire::test(ListProducts::class, ['tenant' => $this->tenant])
            ->assertActionExists('export');
    }
}
