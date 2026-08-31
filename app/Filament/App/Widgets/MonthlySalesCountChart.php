<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class MonthlySalesCountChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Crescimento de pedidos';

    protected ?string $description = 'Quantidade de pedidos efetivados por mês.';

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
                    'label' => 'Pedidos',
                    'data' => $series['count'],
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.10)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'pointBackgroundColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
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
