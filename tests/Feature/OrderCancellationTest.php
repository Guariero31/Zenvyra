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
use App\Services\OrderLifecycleManager;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    private OrderLifecycleManager $lifecycle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $this->lifecycle = app(OrderLifecycleManager::class);
    }

    public function test_pending_order_can_be_canceled_without_touching_stock(): void
    {
        $product = $this->createProduct();
        $variation = ProductVariation::create([
            'product_id' => $product->id,
            'name' => 'Tamanho 40',
            'stock' => 10,
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
            'quantity' => 2,
            'price_at_sale' => 10,
            'cost_at_sale' => 5,
        ]);

        $this->lifecycle->cancel($order);

        $this->assertSame(OrderStatus::Canceled, $order->status);
        $this->assertSame(OrderStatus::Canceled, $order->refresh()->status);
        $this->assertSame(10, $variation->refresh()->stock);
        $this->assertNull($order->inventory_deducted_at);
    }

    public function test_paid_order_cannot_be_canceled(): void
    {
        $order = $this->createPaidOrder();

        $this->expectException(ValidationException::class);

        $this->lifecycle->cancel($order);
    }

    public function test_already_canceled_order_cannot_be_canceled_again(): void
    {
        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => OrderStatus::Canceled,
        ]);

        $this->expectException(ValidationException::class);

        $this->lifecycle->cancel($order);
    }

    public function test_paid_order_keeps_status_after_failed_cancellation(): void
    {
        $order = $this->createPaidOrder();

        try {
            $this->lifecycle->cancel($order);
        } catch (ValidationException) {
            // esperado
        }

        $this->assertSame(OrderStatus::Paid, $order->refresh()->status);
    }

    public function test_leader_can_cancel_but_member_cannot(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja Cancelamento']);
        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => OrderStatus::Pending,
        ]);

        $leader = User::factory()->create();
        $member = User::factory()->create();
        $tenant->users()->attach($leader, ['tu_papel' => TenantPortalRole::Leader->value]);
        $tenant->users()->attach($member, ['tu_papel' => TenantPortalRole::Member->value]);

        $this->actingAs($leader);
        Filament::setCurrentPanel('app');
        Filament::setTenant($tenant);

        $policy = new OrderPolicy;

        $this->assertTrue($policy->cancel($leader, $order));
        $this->assertFalse($policy->cancel($member, $order));
        $this->assertFalse(Gate::forUser($member)->allows('cancel', $order));
    }

    public function test_cancel_action_is_visible_for_pending_orders_only(): void
    {
        $tenant = Tenant::create(['te_name' => 'Loja UI Cancelamento']);
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

        $paidOrder = $this->createPaidOrder();

        Livewire::test(EditOrder::class, ['record' => $pendingOrder->id])
            ->assertActionVisible('cancel');

        Livewire::test(EditOrder::class, ['record' => $paidOrder->id])
            ->assertActionHidden('cancel');
    }

    private function createPaidOrder(): Order
    {
        $product = $this->createProduct();
        $variation = ProductVariation::create([
            'product_id' => $product->id,
            'name' => 'Tamanho 42',
            'stock' => 10,
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
            'quantity' => 1,
            'price_at_sale' => 10,
            'cost_at_sale' => 5,
        ]);

        $order->update(['status' => OrderStatus::Paid]);

        return $order->refresh();
    }

    private function createProduct(): Product
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
            'status' => 'active',
        ]);
    }
}
