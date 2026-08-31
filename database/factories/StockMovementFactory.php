<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'product_variation_id' => null,
            'order_id' => null,
            'type' => StockMovementType::Entry,
            'quantity' => fake()->numberBetween(1, 50),
            'reason' => fake()->optional()->sentence(),
            'user_id' => null,
        ];
    }
}
