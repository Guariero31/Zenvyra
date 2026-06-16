<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\Orders\Pages\EditOrder;
use App\Filament\App\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StockMovement;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderRefundManager;
use App\Services\StockManager;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);
    }

    public function test_manual_entry_increases_product_stock_and_logs_movement(): void
    {
        $product = $this->createProduct(stock: 10);

        app(StockManager::class)->entry($product, null, 20, 'Compra de fornecedor', null);

        $this->assertSame(30, $product->refresh()->stock);

        $movement = StockMovement::query()->latest('id')->first();
        $this->assertSame(StockMovementType::Entry, $movement->type);
        $this->assertSame(20, $movement->quantity);
        $this->assertSame($product->id, $movement->product_id);
        $this->assertNull($movement->product_variation_id);
        $this->assertSame('Compra de fornecedor', $movement->reason);
    }

    public function test_manual_entry_targets_variation_when_provided(): void
    {
        $product = $this->createProduct();
        $variation = ProductVariation::create([
            'product_id' => $product->id,
            'name' => 'Tamanho 40',
            'stock' => 5,
        ]);

        app(StockManager::class)->entry($product, $variation, 15, null, null);

        $this->assertSame(20, $variation->refresh()->stock);

        $movement = StockMovement::query()->latest('id')->first();
        $this->assertSame($variation->id, $movement->product_variation_id);
        $this->assertSame(StockMovementType::Entry, $movement->type);
    }

    public function test_entry_rejects_non_positive_quantity(): void
    {
        $product = $this->createProduct(stock: 10);

        $this->expectException(ValidationException::class);

        app(StockManager::class)->entry($product, null, 0, null, null);
    }

    public function test_sale_logs_stock_movement_when_order_is_paid(): void
    {
        $product = $this->createProduct(stock: 12);
        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => 'pending',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'price_at_sale' => 100,
            'cost_at_sale' => 50,
        ]);

        $order->update(['status' => 'paid']);

        $this->assertSame(8, $product->refresh()->stock);

        $movement = StockMovement::query()->where('type', StockMovementType::Sale)->first();
        $this->assertNotNull($movement);
        $this->assertSame(4, $movement->quantity);
        $this->assertSame($order->id, $movement->order_id);
    }

    public function test_refund_with_restock_logs_movement(): void
    {
        $product = $this->createProduct(stock: 10);
        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => 'pending',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price_at_sale' => 50,
            'cost_at_sale' => 20,
        ]);
        $order->update(['status' => 'paid']);

        $order->refresh()->load('items');

        app(OrderRefundManager::class)->refund(
            order: $order,
            items: [$order->items->first()->id => 2],
            reason: 'Devolução parcial',
            restock: true,
            userId: null,
        );

        $this->assertSame(9, $product->refresh()->stock);

        $movement = StockMovement::query()->where('type', StockMovementType::Refund)->first();
        $this->assertNotNull($movement);
        $this->assertSame(2, $movement->quantity);
        $this->assertSame($order->id, $movement->order_id);
    }

    public function test_is_low_stock_helper(): void
    {
        $low = $this->createProduct(stock: 3);
        $healthy = $this->createProduct(stock: 50);

        $this->assertTrue($low->isLowStock());
        $this->assertFalse($healthy->isLowStock());
    }

    public function test_order_item_cannot_exceed_available_stock(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Estoque UI']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();

        $product = $this->createProduct(stock: 5);
        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => 'pending',
        ]);

        Livewire::test(ItemsRelationManager::class, [
            'ownerRecord' => $order,
            'pageClass' => EditOrder::class,
        ])
            ->callTableAction('create', data: [
                'product_id' => $product->id,
                'quantity' => 99,
                'price_at_sale' => 1000,
                'cost_at_sale' => 500,
            ])
            ->assertHasTableActionErrors(['quantity']);
    }

    private function createProduct(int $stock = 0): Product
    {
        $category = Category::create([
            'name' => 'Tênis',
            'slug' => 'tenis-'.uniqid(),
        ]);

        return Product::create([
            'category_id' => $category->id,
            'title' => 'Tênis Demo',
            'sku' => 'TENIS-'.uniqid(),
            'price' => 199.90,
            'cost_price' => 89.90,
            'stock' => $stock,
            'low_stock_threshold' => 5,
            'status' => 'active',
        ]);
    }
}
