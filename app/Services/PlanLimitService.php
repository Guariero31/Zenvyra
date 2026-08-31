<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Plan;
use App\Models\Product;
use App\Models\Tenant;
use Filament\Facades\Filament;
use RuntimeException;

class PlanLimitService
{
    public function planFor(Tenant $tenant): ?Plan
    {
        $subscription = $tenant->subscriptions()->latest('id')->first();

        return $subscription?->plan;
    }

    public function assertCanAddProduct(Tenant $tenant): void
    {
        $plan = $this->planFor($tenant);

        if (! $plan instanceof Plan || $plan->max_products === null) {
            return;
        }

        if ($this->productCount($tenant) >= $plan->max_products) {
            throw new RuntimeException(
                "Limite de {$plan->max_products} produtos do plano {$plan->name} atingido."
            );
        }
    }

    public function assertCanAddUser(Tenant $tenant): void
    {
        $plan = $this->planFor($tenant);

        if (! $plan instanceof Plan || $plan->max_users === null) {
            return;
        }

        if ($tenant->users()->count() >= $plan->max_users) {
            throw new RuntimeException(
                "Limite de {$plan->max_users} usuários do plano {$plan->name} atingido."
            );
        }
    }

    public function productLimitMessage(Tenant $tenant): ?string
    {
        $plan = $this->planFor($tenant);

        if (! $plan instanceof Plan || $plan->max_products === null) {
            return null;
        }

        return "Plano {$plan->name}: até {$plan->max_products} produtos ({$this->productCount($tenant)} cadastrados).";
    }

    private function productCount(Tenant $tenant): int
    {
        $currentTenant = Filament::getTenant();

        if ($currentTenant?->is($tenant)) {
            return Product::count();
        }

        return 0;
    }
}
