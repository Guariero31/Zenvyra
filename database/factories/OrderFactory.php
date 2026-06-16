<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderOrigin;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'user_id' => null,
            'origin' => fake()->randomElement(OrderOrigin::cases()),
            'total_amount' => 0,
            'payment_method' => fake()->randomElement(['dinheiro', 'pix', 'cartao_credito', 'cartao_debito']),
            'status' => OrderStatus::Pending,
        ];
    }
}
