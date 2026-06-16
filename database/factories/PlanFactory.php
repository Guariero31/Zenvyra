<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BillingPeriod;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 49, 499),
            'currency' => 'BRL',
            'billing_period' => fake()->randomElement(BillingPeriod::cases()),
            'trial_days' => fake()->randomElement([0, 7, 14]),
            'max_products' => fake()->randomElement([null, 500, 5000]),
            'max_users' => fake()->randomElement([null, 2, 10]),
            'is_active' => true,
            'features' => [
                'usuarios' => (string) fake()->numberBetween(1, 50),
                'produtos' => (string) fake()->numberBetween(100, 10000),
            ],
        ];
    }
}
