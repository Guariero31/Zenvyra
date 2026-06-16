<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 20, 500);

        return [
            'category_id' => Category::factory(),
            'title' => Str::title(fake()->words(3, true)),
            'description' => fake()->paragraph(),
            'sku' => strtoupper(Str::random(8)),
            'price' => $price,
            'cost_price' => round($price * fake()->randomFloat(2, 0.4, 0.7), 2),
            'commission_rate' => null,
            'stock' => fake()->numberBetween(10, 100),
            'low_stock_threshold' => 5,
            'status' => 'active',
        ];
    }
}
