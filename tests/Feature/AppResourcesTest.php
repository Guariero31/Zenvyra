<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\TenantPortalRole;
use App\Filament\App\Resources\Categories\Pages\CreateCategory;
use App\Filament\App\Resources\Categories\Pages\EditCategory;
use App\Filament\App\Resources\Categories\Pages\ListCategories;
use App\Filament\App\Resources\Customers\Pages\CreateCustomer;
use App\Filament\App\Resources\Customers\Pages\EditCustomer;
use App\Filament\App\Resources\Customers\Pages\ListCustomers;
use App\Filament\App\Resources\Orders\Pages\CreateOrder;
use App\Filament\App\Resources\Orders\Pages\EditOrder;
use App\Filament\App\Resources\Orders\Pages\ListOrders;
use App\Filament\App\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Filament\App\Resources\Products\Pages\CreateProduct;
use App\Filament\App\Resources\Products\Pages\EditProduct;
use App\Filament\App\Resources\Products\Pages\ListProducts;
use App\Filament\App\Resources\Products\RelationManagers\ImagesRelationManager;
use App\Filament\App\Resources\TeamMembers\Pages\CreateTeamMember;
use App\Filament\App\Resources\TeamMembers\Pages\ListTeamMembers;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppResourcesTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);

        $this->tenant = Tenant::create(['te_name' => 'Loja App Teste']);

        $user = User::factory()->create();
        $this->tenant->users()->attach($user, [
            'tu_papel' => TenantPortalRole::AdministradorCliente->value,
        ]);

        $this->actingAs($user);

        Filament::setCurrentPanel('app');
        Filament::setTenant($this->tenant);
        Filament::bootCurrentPanel();
    }

    public function test_app_category_resource_pages_can_load(): void
    {
        $category = Category::create([
            'name' => 'Tênis',
            'slug' => 'tenis',
        ]);

        Livewire::test(ListCategories::class)->assertOk();
        Livewire::test(CreateCategory::class)->assertOk();
        Livewire::test(EditCategory::class, ['record' => $category->id])->assertOk();
    }

    public function test_app_product_resource_pages_can_load(): void
    {
        $category = Category::create([
            'name' => 'Tênis',
            'slug' => 'tenis',
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Tênis Demo',
            'description' => 'Produto de teste',
            'sku' => 'TENIS-DEMO',
            'price' => 199.90,
            'cost_price' => 89.90,
            'status' => 'active',
        ]);

        Livewire::test(ListProducts::class)->assertOk();
        Livewire::test(CreateProduct::class)->assertOk();
        Livewire::test(EditProduct::class, ['record' => $product->id])
            ->assertActionVisible('toggleStatus')
            ->assertOk();
    }

    public function test_product_images_gallery_loads_with_upload_action(): void
    {
        $category = Category::create([
            'name' => 'Tênis',
            'slug' => 'tenis-galeria',
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Tênis Galeria',
            'sku' => 'TENIS-GALERIA',
            'price' => 199.90,
            'cost_price' => 89.90,
            'status' => 'active',
        ]);
        $product->images()->create(['image_path' => 'https://picsum.photos/seed/demo/600/600']);

        Livewire::test(ImagesRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => EditProduct::class,
        ])
            ->assertTableActionVisible('uploadImages')
            ->assertOk();
    }

    public function test_product_status_can_be_toggled_from_edit_page(): void
    {
        $category = Category::create([
            'name' => 'Tênis',
            'slug' => 'tenis-toggle',
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Tênis Toggle',
            'sku' => 'TENIS-TOGGLE',
            'price' => 199.90,
            'cost_price' => 89.90,
            'status' => 'active',
        ]);

        Livewire::test(EditProduct::class, ['record' => $product->id])
            ->callAction('toggleStatus')
            ->assertNotified();

        $this->assertSame('inactive', $product->refresh()->status);

        Livewire::test(EditProduct::class, ['record' => $product->id])
            ->callAction('toggleStatus')
            ->assertNotified();

        $this->assertSame('active', $product->refresh()->status);
    }

    public function test_app_customer_resource_pages_can_load(): void
    {
        $customer = Customer::create([
            'name' => 'Cliente Demo',
            'phone' => '11999999999',
            'email' => 'cliente@example.com',
        ]);

        Livewire::test(ListCustomers::class)->assertOk();
        Livewire::test(CreateCustomer::class)->assertOk();
        Livewire::test(EditCustomer::class, ['record' => $customer->id])->assertOk();
    }

    public function test_app_team_resource_pages_can_load(): void
    {
        Livewire::test(ListTeamMembers::class)->assertOk();
        Livewire::test(CreateTeamMember::class)->assertOk();
    }

    public function test_app_order_resource_pages_can_load(): void
    {
        $customer = Customer::create(['name' => 'Cliente Pedido']);
        $order = Order::create([
            'customer_id' => $customer->id,
            'user_id' => auth()->id(),
            'origin' => 'BALCAO',
            'total_amount' => 199.90,
            'payment_method' => 'pix',
            'status' => 'pending',
        ]);

        Livewire::test(ListOrders::class)->assertOk();
        Livewire::test(CreateOrder::class)->assertOk();
        Livewire::test(EditOrder::class, ['record' => $order->id])->assertOk();
    }

    public function test_list_orders_tabs_filter_by_status(): void
    {
        $paid = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 100,
            'status' => OrderStatus::Paid,
        ]);

        $canceled = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 50,
            'status' => OrderStatus::Canceled,
        ]);

        Livewire::test(ListOrders::class)
            ->assertCanSeeTableRecords([$paid, $canceled])
            ->set('activeTab', OrderStatus::Paid->value)
            ->assertCanSeeTableRecords([$paid])
            ->assertCanNotSeeTableRecords([$canceled]);
    }

    public function test_order_items_can_be_managed_while_pending(): void
    {
        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => 'pending',
        ]);

        Livewire::test(ItemsRelationManager::class, [
            'ownerRecord' => $order,
            'pageClass' => EditOrder::class,
        ])
            ->assertTableActionVisible('create')
            ->assertOk();
    }

    public function test_order_items_are_locked_once_paid(): void
    {
        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => 'paid',
        ]);

        Livewire::test(ItemsRelationManager::class, [
            'ownerRecord' => $order,
            'pageClass' => EditOrder::class,
        ])
            ->assertTableActionHidden('create')
            ->assertOk();
    }
}
