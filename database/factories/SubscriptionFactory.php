<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $start = now();
        $cycleEnd = $start->copy()->addDays((int) config('subscriptions.billing_cycle_days'));

        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::Active,
            'starts_at' => $start,
            'trial_ends_at' => null,
            'ends_at' => $cycleEnd,
            'grace_ends_at' => $cycleEnd->copy()->addDays((int) config('subscriptions.grace_days')),
            'next_billing_at' => $cycleEnd,
            'canceled_at' => null,
        ];
    }

    public function trialing(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::Trialing,
            'trial_ends_at' => now()->addDays((int) config('subscriptions.default_trial_days')),
            'next_billing_at' => null,
        ]);
    }

    public function pastDue(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::PastDue,
            'grace_ends_at' => now()->addDays((int) config('subscriptions.grace_days')),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::Expired,
            'ends_at' => now()->subDay(),
            'grace_ends_at' => now()->subDay(),
            'next_billing_at' => null,
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::Canceled,
            'canceled_at' => now(),
            'next_billing_at' => null,
        ]);
    }
}
