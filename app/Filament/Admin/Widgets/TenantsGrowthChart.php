<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Models\Tenant;
use Filament\Widgets\ChartWidget;

class TenantsGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Crescimento de lojas';

    protected ?string $maxHeight = '190px';

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = ['default' => 1, 'xl' => 2];

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $monthAbbreviations = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        $labels = [];
        $values = [];

        foreach (range(5, 0) as $offset) {
            $month = now()->startOfMonth()->subMonths($offset);
            $labels[] = $monthAbbreviations[$month->month].'/'.$month->format('y');
            $values[] = Tenant::whereBetween('created_at', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Novas lojas',
                    'data' => $values,
                    'borderColor' => '#739c24',
                    'backgroundColor' => 'rgba(163, 211, 49, 0.12)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
