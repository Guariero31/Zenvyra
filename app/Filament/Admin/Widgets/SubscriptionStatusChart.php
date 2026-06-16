<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Filament\Widgets\ChartWidget;

class SubscriptionStatusChart extends ChartWidget
{
    protected ?string $heading = 'Assinaturas por status';

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $colors = [
            SubscriptionStatus::Trialing->value => '#3b82f6',
            SubscriptionStatus::Active->value => '#10b981',
            SubscriptionStatus::PastDue->value => '#f59e0b',
            SubscriptionStatus::Canceled->value => '#6b7280',
            SubscriptionStatus::Expired->value => '#f43f5e',
        ];

        $counts = Subscription::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [];
        $data = [];
        $backgroundColor = [];

        foreach (SubscriptionStatus::cases() as $status) {
            $labels[] = $status->getLabel();
            $data[] = (int) ($counts[$status->value] ?? 0);
            $backgroundColor[] = $colors[$status->value];
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
