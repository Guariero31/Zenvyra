<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Enums\TenantPortalRole;
use App\Filament\App\Pages\PdvPage;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PdvFinalizeOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);
    }

    public function test_finalize_order_deducts_stock_and_logs_movement(): void
    {
        $this->actingAsSeller();
        $product = $this->createProduct(stock: 10, price: '100.00');

        Livewire::test(PdvPage::class)
            ->set('cart', [$this->cartItem($product, quantity: 3)])
            ->call('finalizeOrder');

        $this->assertSame(7, $product->refresh()->stock);

        $order = Order::query()->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertNotNull($order->inventory_deducted_at);

        $movement = StockMovement::query()->where('type', StockMovementType::Sale)->first();
        $this->assertNotNull($movement);
        $this->assertSame(3, $movement->quantity);
        $this->assertSame($product->id, $movement->product_id);
        $this->assertSame($order->id, $movement->order_id);
    }

    public function test_finalize_order_preserves_discount_on_total_amount(): void
    {
        $this->actingAsSeller();
        $product = $this->createProduct(stock: 10, price: '100.00');

        $page = new PdvPage;
        $page->cart = [$this->cartItem($product, quantity: 2)];
        $page->discountAmount = '20';
        $page->finalizeOrder();

        $order = Order::query()->latest('id')->first();

        // Subtotal (2 x 100.00 = 200.00) minus a 20.00 discount.
        // OrderItemObserver::created() fires OrderLifecycleManager::
        // recalculateTotal() on every OrderItem insert, which recomputes
        // total_amount from undiscounted line totals — this asserts the
        // discount survives that clobbering.
        $this->assertSame('180.00', $order->total_amount);
    }

    public function test_finalize_order_uses_real_cost_price_for_margin_tracking(): void
    {
        $this->actingAsSeller();
        $product = $this->createProduct(stock: 10, price: '100.00', costPrice: '42.50');

        Livewire::test(PdvPage::class)
            ->set('cart', [$this->cartItem($product, quantity: 1)])
            ->call('finalizeOrder');

        $order = Order::query()->latest('id')->first();
        $item = $order->items()->first();

        $this->assertSame('42.50', $item->cost_at_sale);
    }

    public function test_finalize_order_rolls_back_entirely_when_stock_is_insufficient(): void
    {
        $this->actingAsSeller();
        $product = $this->createProduct(stock: 2, price: '100.00');

        Livewire::test(PdvPage::class)
            ->set('cart', [$this->cartItem($product, quantity: 5)])
            ->call('finalizeOrder');

        // Negative stock is disabled by default, so the sale must be
        // rejected: no order created, no stock movement, stock untouched.
        $this->assertSame(2, $product->refresh()->stock);
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(0, StockMovement::query()->count());
    }

    private function actingAsSeller(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja PDV Teste']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();
        session(['tenant_id' => $tenant->getKey()]);
    }

    private function createProduct(int $stock, string $price, string $costPrice = '0.00'): Product
    {
        $category = Category::create([
            'name' => 'Categoria PDV',
            'slug' => 'categoria-pdv-'.uniqid(),
        ]);

        return Product::create([
            'category_id' => $category->id,
            'title' => 'Produto PDV',
            'sku' => 'PDV-'.uniqid(),
            'price' => $price,
            'cost_price' => $costPrice,
            'stock' => $stock,
            'status' => 'active',
        ]);
    }

    /** @return array<string, mixed> */
    private function cartItem(Product $product, int $quantity): array
    {
        return [
            'key' => $product->id.'-0',
            'product_id' => $product->id,
            'variation_id' => null,
            'name' => $product->title,
            'variation_name' => null,
            'sku' => $product->sku,
            'price' => (string) $product->price,
            'commission_rate' => '0',
            'quantity' => $quantity,
            'stock' => $product->stock,
        ];
    }
}
