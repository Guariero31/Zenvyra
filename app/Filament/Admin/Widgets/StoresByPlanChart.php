<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use Filament\Widgets\ChartWidget;

class StoresByPlanChart extends ChartWidget
{
    protected ?string $heading = 'Lojas ativas por plano';

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $liveStatuses = collect(SubscriptionStatus::cases())
            ->filter(fn (SubscriptionStatus $status): bool => $status->isLive())
            ->map(fn (SubscriptionStatus $status): string => $status->value)
            ->all();

        $plans = Plan::query()
            ->withCount(['subscriptions' => fn ($query) => $query->whereIn('status', $liveStatuses)])
            ->orderBy('name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Lojas ativas',
                    'data' => $plans->pluck('subscriptions_count')->all(),
                    'backgroundColor' => '#6366f1',
                ],
            ],
            'labels' => $plans->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
