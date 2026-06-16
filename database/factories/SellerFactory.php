<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seller>
 */
class SellerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'user_id' => null,
            'commission_rate' => fake()->randomFloat(2, 1, 10),
            'active' => true,
        ];
    }

    public function linkedToUser(?User $user = null): static
    {
        return $this->state(fn (): array => [
            'user_id' => ($user ?? User::factory()->create())->id,
        ]);
    }
}
