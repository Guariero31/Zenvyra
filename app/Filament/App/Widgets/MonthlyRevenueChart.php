<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Receita Mensal';

    protected ?string $description = 'Faturamento bruto e líquido por mês.';

    protected int|string|array $columnSpan = 2;

    public ?string $filter = '12';

    public function getHeight(): string|int|null
    {
        return 240;
    }

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
                    'borderColor' => 'rgb(70, 72, 212)',
                    'backgroundColor' => 'rgba(70, 72, 212, 0.10)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'pointBackgroundColor' => 'rgb(70, 72, 212)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Faturamento líquido (R$)',
                    'data' => $series['net'],
                    'borderColor' => 'rgb(0, 110, 45)',
                    'backgroundColor' => 'rgba(0, 110, 45, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                    'pointBackgroundColor' => 'rgb(0, 110, 45)',
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
                    'grid' => ['color' => 'rgba(0,0,0,0.04)'],
                    'ticks' => ['color' => '#767586'],
                ],
                'y' => [
                    'grid' => ['color' => 'rgba(0,0,0,0.04)'],
                    'ticks' => ['color' => '#767586'],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
