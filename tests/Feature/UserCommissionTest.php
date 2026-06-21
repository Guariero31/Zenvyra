<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\OrderLifecycleManager;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCommissionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $this->tenant = Tenant::create(['te_name' => 'Loja Comissão']);
        $this->manager = User::factory()->create();
        $this->tenant->users()->attach($this->manager, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
            'tu_commission_rate' => 0,
        ]);

        $this->bootTenantPanel($this->manager);
    }

    public function test_product_commission_overrides_user_default_commission(): void
    {
        $sellerUser = User::factory()->create();
        $this->tenant->users()->attach($sellerUser, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
            'tu_commission_rate' => 5,
        ]);

        $product = Product::factory()->create(['commission_rate' => 12.5]);
        $order = Order::factory()->create(['user_id' => $sellerUser->id]);

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_at_sale' => 100,
        ]);

        app(OrderLifecycleManager::class)->recalculateTotal($order->fresh());

        $this->assertSame('12.50', $item->refresh()->commission_rate);
        $this->assertSame('25.00', $item->commission_amount);
        $this->assertSame('25.00', $order->refresh()->commission_amount);
    }

    public function test_user_default_commission_is_used_when_product_has_no_rate(): void
    {
        $sellerUser = User::factory()->create();
        $this->tenant->users()->attach($sellerUser, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
            'tu_commission_rate' => 7.5,
        ]);

        $product = Product::factory()->create(['commission_rate' => null]);
        $order = Order::factory()->create(['user_id' => $sellerUser->id]);

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price_at_sale' => 20,
        ]);

        app(OrderLifecycleManager::class)->recalculateTotal($order->fresh());

        $this->assertSame('7.50', $item->refresh()->commission_rate);
        $this->assertSame('4.50', $item->commission_amount);
        $this->assertSame('4.50', $order->refresh()->commission_amount);
    }

    public function test_salesperson_only_sees_their_own_orders(): void
    {
        $sellerUser = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->tenant->users()->attach($sellerUser, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
        ]);
        $this->tenant->users()->attach($otherUser, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
        ]);

        $ownOrder = Order::factory()->create(['user_id' => $sellerUser->id]);
        $otherOrder = Order::factory()->create(['user_id' => $otherUser->id]);

        $this->bootTenantPanel($sellerUser);

        $ids = OrderResource::getEloquentQuery()
            ->pluck('id')
            ->all();

        $this->assertContains($ownOrder->id, $ids);
        $this->assertNotContains($otherOrder->id, $ids);
    }

    public function test_user_commission_rate_is_read_from_tenant_pivot(): void
    {
        $sellerUser = User::factory()->create();
        $this->tenant->users()->attach($sellerUser, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
            'tu_commission_rate' => 9.25,
        ]);

        $this->assertSame('9.25', $sellerUser->commissionRateInTenant($this->tenant));
    }

    private function bootTenantPanel(User $user): void
    {
        $this->actingAs($user);

        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
        Filament::bootCurrentPanel();
    }
}
