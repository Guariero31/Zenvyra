<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 20, 500);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_variation_id' => null,
            'quantity' => fake()->numberBetween(1, 5),
            'price_at_sale' => $price,
            'cost_at_sale' => round($price * 0.5, 2),
            'commission_rate' => 0,
            'commission_amount' => 0,
        ];
    }
}
