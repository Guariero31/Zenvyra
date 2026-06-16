<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--path' => 'database/migrations/tenant',
        ]);
    }

    public function test_order_total_is_recalculated_when_items_change(): void
    {
        $product = $this->createProduct();
        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => 'pending',
        ]);

        $firstItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price_at_sale' => 10,
            'cost_at_sale' => 4,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_sale' => 15,
            'cost_at_sale' => 7,
        ]);

        $this->assertSame('35.00', $order->refresh()->total_amount);

        $firstItem->update(['quantity' => 3]);

        $this->assertSame('45.00', $order->refresh()->total_amount);

        $firstItem->delete();

        $this->assertSame('15.00', $order->refresh()->total_amount);
    }

    public function test_inventory_is_deducted_once_when_order_is_marked_as_paid(): void
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
            'status' => 'pending',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variation_id' => $variation->id,
            'quantity' => 3,
            'price_at_sale' => 100,
            'cost_at_sale' => 50,
        ]);

        $order->update(['status' => 'paid']);

        $this->assertSame(7, $variation->refresh()->stock);
        $this->assertNotNull($order->refresh()->inventory_deducted_at);

        $order->update(['status' => 'paid']);

        $this->assertSame(7, $variation->refresh()->stock);
    }

    public function test_inventory_is_deducted_from_product_when_order_has_no_variation(): void
    {
        $product = $this->createProduct(stock: 20);
        $order = Order::create([
            'origin' => 'BALCAO',
            'total_amount' => 0,
            'status' => 'pending',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'price_at_sale' => 100,
            'cost_at_sale' => 50,
        ]);

        $order->update(['status' => 'paid']);

        $this->assertSame(15, $product->refresh()->stock);
        $this->assertNotNull($order->refresh()->inventory_deducted_at);
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
            'sku' => 'TENIS-DEMO',
            'price' => 199.90,
            'cost_price' => 89.90,
            'stock' => $stock,
            'status' => 'active',
        ]);
    }
}
