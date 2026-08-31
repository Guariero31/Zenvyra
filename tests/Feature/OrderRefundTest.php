<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\Orders\Pages\EditOrder;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Services\OrderRefundManager;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OrderRefundTest extends TestCase
{
    use RefreshDatabase;

    private OrderRefundManager $refunds;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $this->refunds = app(OrderRefundManager::class);
    }

    public function test_full_refund_restock_variations_and_marks_order_refunded(): void
    {
        [$order, $variation] = $this->createPaidOrderWithVariation(stock: 10, quantity: 3);
        $user = User::factory()->create();

        $refund = $this->refunds->refund(
            order: $order,
            items: [$order->items->first()->id => 3],
            reason: 'Cliente desistiu',
            restock: true,
            userId: $user->id,
        );

        $order->refresh();
        $variation->refresh();

        $this->assertSame('30.00', (string) $order->refunded_amount);
        $this->assertSame(OrderStatus::Refunded, $order->status);
        $this->assertNotNull($order->refunded_at);
        $this->assertSame(10, $variation->stock);
        $this->assertSame('30.00', (string) $refund->amount);
        $this->assertTrue($refund->restocked);
        $this->assertSame($user->id, $refund->refunded_by);
        $this->assertSame('Cliente desistiu', $refund->reason);
    }

    public function test_partial_refund_restock_only_refunded_quantity(): void
    {
        [$order, $variation] = $this->createPaidOrderWithVariation(stock: 10, quantity: 5);
        $itemId = $order->items->first()->id;

        $this->refunds->refund(
            order: $order,
            items: [$itemId => 2],
            reason: 'Troca parcial',
            restock: true,
            userId: null,
        );

        $order->refresh();
        $variation->refresh();

        $this->assertSame('20.00', (string) $order->refunded_amount);
        $this->assertSame(OrderStatus::PartiallyRefunded, $order->status);
        $this->assertNull($order->refunded_at);
        $this->assertSame(7, $variation->stock);
        $this->assertSame(3, $order->items->first()->refundableQuantity());
    }

    public function test_cumulative_partial_refunds_can_fully_refund_order(): void
    {
        [$order, $variation] = $this->createPaidOrderWithVariation(stock: 10, quantity: 4);
        $itemId = $order->items->first()->id;

        $this->refunds->refund($order, [$itemId => 1], 'Primeiro estorno', true, null);
        $this->refunds->refund($order->refresh(), [$itemId => 3], 'Segundo estorno', true, null);

        $order->refresh();
        $variation->refresh();

        $this->assertSame('40.00', (string) $order->refunded_amount);
        $this->assertSame(OrderStatus::Refunded, $order->status);
        $this->assertNotNull($order->refunded_at);
        $this->assertSame(10, $variation->stock);
        $this->assertSame(0, $order->items->first()->refundableQuantity());
    }

    public function test_refund_without_restock_does_not_change_inventory(): void
    {
        [$order, $variation] = $this->createPaidOrderWithVariation(stock: 8, quantity: 2);
        $itemId = $order->items->first()->id;

        $refund = $this->refunds->refund($order, [$itemId => 2], 'Produto danificado', false, null);

        $variation->refresh();

        $this->assertSame(6, $variation->stock);
        $this->assertFalse($refund->restocked);
        $this->assertSame(OrderStatus::Refunded, $order->refresh()->status);
    }

    public function test_refund_without_variation_restock_product_stock(): void
    {
        $product = $this->createProduct(stock: 20);
        $order = $this->createPaidOrderWithoutVariation(product: $product, quantity: 2, price: 15);

        $this->refunds->refund(
            order: $order,
            items: [$order->items->first()->id => 2],
            reason: 'Estorno sem variação',
            restock: true,
            userId: null,
        );

        $order->refresh();
        $product->refresh();

        $this->assertSame('30.00', (string) $order->refunded_amount);
        $this->assertSame(OrderStatus::Refunded, $order->status);
        $this->assertSame(20, $product->stock);
    }

    public function test_refund_rejects_non_refundable_statuses(): void
    {
        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 10,
            'status' => OrderStatus::Pending,
        ]);

        $this->expectException(ValidationException::class);

        $this->refunds->refund($order, [1 => 1], 'Motivo', true, null);
    }

    public function test_refund_rejects_quantity_above_refundable_balance(): void
    {
        [$order] = $this->createPaidOrderWithVariation(stock: 10, quantity: 2);
        $itemId = $order->items->first()->id;

        $this->expectException(ValidationException::class);

        $this->refunds->refund($order, [$itemId => 3], 'Quantidade inválida', true, null);
    }

    public function test_refund_requires_reason(): void
    {
        [$order] = $this->createPaidOrderWithVariation(stock: 10, quantity: 1);
        $itemId = $order->items->first()->id;

        $this->expectException(ValidationException::class);

        $this->refunds->refund($order, [$itemId => 1], '   ', true, null);
    }

    public function test_leader_can_refund_but_member_cannot(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Estorno']);
        [$order] = $this->createPaidOrderWithVariation(stock: 5, quantity: 1);

        $leader = User::factory()->create();
        $member = User::factory()->create();

        $tenant->users()->attach($leader, ['tu_papel' => TenantPortalRole::Leader->value]);
        $tenant->users()->attach($member, ['tu_papel' => TenantPortalRole::Member->value]);

        $this->actingAs($leader);

        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);

        $policy = new OrderPolicy;

        $this->assertTrue($policy->refund($leader, $order));
        $this->assertFalse($policy->refund($member, $order));
        $this->assertFalse(Gate::forUser($member)->allows('refund', $order));
    }

    public function test_refund_action_is_visible_for_paid_orders_only(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja UI Estorno']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();

        $pendingOrder = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => OrderStatus::Pending,
        ]);

        [$paidOrder] = $this->createPaidOrderWithVariation(stock: 5, quantity: 1);

        Livewire::test(EditOrder::class, ['record' => $pendingOrder->id])
            ->assertActionHidden('refund');

        Livewire::test(EditOrder::class, ['record' => $paidOrder->id])
            ->assertActionVisible('refund');
    }

    public function test_refund_action_form_mounts_with_table_repeater(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Modal Estorno']);
        $user = User::factory()->create();
        $tenant->users()->attach($user, ['tu_papel' => TenantPortalRole::AdministradorCliente->value]);

        $this->actingAs($user);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);
        Filament::bootCurrentPanel();

        [$paidOrder] = $this->createPaidOrderWithVariation(stock: 5, quantity: 3);

        Livewire::test(EditOrder::class, ['record' => $paidOrder->id])
            ->mountAction('refund')
            ->assertActionMounted('refund');
    }

    /**
     * @return array{0: Order, 1: ProductVariation}
     */
    private function createPaidOrderWithVariation(int $stock, int $quantity): array
    {
        $product = $this->createProduct();
        $variation = ProductVariation::create([
            'product_id' => $product->id,
            'name' => 'Tamanho 40',
            'stock' => $stock,
        ]);

        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => OrderStatus::Pending,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variation_id' => $variation->id,
            'quantity' => $quantity,
            'price_at_sale' => 10,
            'cost_at_sale' => 5,
        ]);

        $order->update(['status' => OrderStatus::Paid]);

        return [$order->refresh()->load('items'), $variation];
    }

    private function createPaidOrderWithoutVariation(int $quantity, float $price, ?Product $product = null): Order
    {
        $product ??= $this->createProduct(stock: 20);

        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => OrderStatus::Pending,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price_at_sale' => $price,
            'cost_at_sale' => 5,
        ]);

        $order->update(['status' => OrderStatus::Paid]);

        return $order->refresh()->load('items');
    }

    private function createProduct(int $stock = 0): Product
    {
        $category = Category::create([
            'name' => 'Tênis',
            'slug' => 'tenis',
        ]);

        return Product::create([
            'category_id' => $category->id,
            'title' => 'Tênis Demo',
            'sku' => 'TENIS-'.uniqid(),
            'price' => 199.90,
            'cost_price' => 89.90,
            'stock' => $stock,
            'status' => 'active',
        ]);
    }
}
