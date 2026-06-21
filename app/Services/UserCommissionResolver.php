<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;

class UserCommissionResolver
{
    public function defaultRate(User $user, Tenant $tenant): string
    {
        $pivot = $user->tenants()
            ->where('te_tenants.id', $tenant->getKey())
            ->first()
            ?->pivot;

        return (string) ($pivot->tu_commission_rate ?? 0);
    }

    public function defaultRateForOrder(Order $order, ?Tenant $tenant = null): string
    {
        if ($order->user_id === null) {
            return '0';
        }

        $user = $order->relationLoaded('operator')
            ? $order->operator
            : $order->operator()->first();

        if (! $user instanceof User) {
            return '0';
        }

        $tenant ??= Filament::getTenant();

        if (! $tenant instanceof Tenant) {
            return '0';
        }

        return $this->defaultRate($user, $tenant);
    }
}
