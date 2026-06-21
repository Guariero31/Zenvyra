<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Faturamento mensal';

    protected ?string $description = 'Faturamento bruto e líquido por mês.';

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = '12';

    protected function getFilters(): ?array
    {
        return [
            '6' => 'Últimos 6 meses',
            '12' => 'Últimos 12 meses',
        ];
    }

    protected function getData(): array
    {
        $series = app(SalesReportService::class)->monthlySeries((int) $this->filter);

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento bruto (R$)',
                    'data' => $series['gross'],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.12)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Faturamento líquido (R$)',
                    'data' => $series['net'],
                    'borderColor' => 'rgba(255, 255, 255, 0.35)',
                    'backgroundColor' => 'rgba(255, 255, 255, 0.03)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                    'pointBackgroundColor' => 'rgba(255, 255, 255, 0.5)',
                    'borderWidth' => 1.5,
                    'borderDash' => [6, 4],
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => ['usePointStyle' => true, 'padding' => 16],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['color' => 'rgba(255,255,255,0.04)'],
                    'ticks' => ['color' => 'rgba(255,255,255,0.45)'],
                ],
                'y' => [
                    'grid' => ['color' => 'rgba(255,255,255,0.04)'],
                    'ticks' => ['color' => 'rgba(255,255,255,0.45)'],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
