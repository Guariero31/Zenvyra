<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\Tenant;
use Filament\Widgets\Widget;

class PlatformOverviewWidget extends Widget
{
    protected string $view = 'filament.admin.widgets.platform-overview';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $totalStores = Tenant::count();
        $activeStores = Tenant::where('te_ativo', true)->count();

        $monthlyRevenue = Subscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->with('plan:id,price')
            ->get()
            ->sum(fn (Subscription $subscription): float => (float) ($subscription->plan?->price ?? 0));

        $upcoming = Subscription::query()
            ->whereNotNull('next_billing_at')
            ->whereBetween('next_billing_at', [now(), now()->addDays(7)])
            ->whereIn('status', [
                SubscriptionStatus::Active->value,
                SubscriptionStatus::PastDue->value,
            ])
            ->count();

        return [
            'totalStores' => $totalStores,
            'activeStores' => $activeStores,
            'activePercentage' => $totalStores > 0 ? (int) round($activeStores / $totalStores * 100) : 0,
            'monthlyRevenue' => $monthlyRevenue,
            'upcoming' => $upcoming,
        ];
    }
}
