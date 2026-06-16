<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\Orders\OrderResource;
use App\Filament\App\Resources\Sellers\Pages\CreateSeller;
use App\Filament\App\Resources\Sellers\Pages\EditSeller;
use App\Filament\App\Resources\Sellers\Pages\ListSellers;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SellerCommissionTest extends TestCase
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
        ]);

        $this->bootTenantPanel($this->manager);
    }

    public function test_seller_resource_pages_can_load(): void
    {
        $seller = Seller::factory()->create();

        Livewire::test(ListSellers::class)->assertOk();
        Livewire::test(CreateSeller::class)->assertOk();
        Livewire::test(EditSeller::class, ['record' => $seller->id])->assertOk();
    }

    public function test_product_commission_overrides_seller_default_commission(): void
    {
        $seller = Seller::factory()->create(['commission_rate' => 5]);
        $product = Product::factory()->create(['commission_rate' => 12.5]);
        $order = Order::factory()->create(['seller_id' => $seller->id]);

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_at_sale' => 100,
        ]);

        $this->assertSame('12.50', $item->refresh()->commission_rate);
        $this->assertSame('25.00', $item->commission_amount);
        $this->assertSame('25.00', $order->refresh()->commission_amount);
    }

    public function test_seller_default_commission_is_used_when_product_has_no_rate(): void
    {
        $seller = Seller::factory()->create(['commission_rate' => 7.5]);
        $product = Product::factory()->create(['commission_rate' => null]);
        $order = Order::factory()->create(['seller_id' => $seller->id]);

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'price_at_sale' => 20,
        ]);

        $this->assertSame('7.50', $item->refresh()->commission_rate);
        $this->assertSame('4.50', $item->commission_amount);
        $this->assertSame('4.50', $order->refresh()->commission_amount);
    }

    public function test_backfill_creates_sellers_from_historical_order_users(): void
    {
        $sellerUser = User::factory()->create(['name' => 'Vendedor Histórico']);
        Order::factory()->create(['user_id' => $sellerUser->id, 'seller_id' => null]);

        $this->artisan('sellers:backfill')->assertSuccessful();
        $this->artisan('sellers:backfill')->assertSuccessful();

        $seller = Seller::where('user_id', $sellerUser->id)->first();

        $this->assertNotNull($seller);
        $this->assertSame('Vendedor Histórico', $seller->name);
        $this->assertSame(1, Seller::where('user_id', $sellerUser->id)->count());
        $this->assertSame($seller->id, Order::where('user_id', $sellerUser->id)->value('seller_id'));
    }

    public function test_salesperson_only_sees_their_own_orders(): void
    {
        $sellerUser = User::factory()->create();
        $this->tenant->users()->attach($sellerUser, [
            'tu_papel' => TenantPortalRole::Vendedor->value,
        ]);
        $seller = Seller::factory()->create(['user_id' => $sellerUser->id]);
        $otherSeller = Seller::factory()->create();
        $ownOrder = Order::factory()->create(['seller_id' => $seller->id]);
        $fallbackOwnOrder = Order::factory()->create(['user_id' => $sellerUser->id, 'seller_id' => null]);
        $otherOrder = Order::factory()->create(['seller_id' => $otherSeller->id]);

        $this->bootTenantPanel($sellerUser);

        $ids = OrderResource::getEloquentQuery()
            ->pluck('id')
            ->all();

        $this->assertContains($ownOrder->id, $ids);
        $this->assertContains($fallbackOwnOrder->id, $ids);
        $this->assertNotContains($otherOrder->id, $ids);
    }

    private function bootTenantPanel(User $user): void
    {
        $this->actingAs($user);

        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
        Filament::bootCurrentPanel();
    }
}
