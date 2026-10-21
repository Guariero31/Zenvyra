<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Vendas no período';

    protected ?string $description = 'Faturamento bruto e líquido por mês.';

    protected ?string $maxHeight = '190px';

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = ['default' => 1, 'xl' => 3];

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
                    'borderColor' => '#64748b',
                    'backgroundColor' => 'rgba(100, 116, 139, 0.12)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'pointBackgroundColor' => '#64748b',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Faturamento líquido (R$)',
                    'data' => $series['net'],
                    'borderColor' => '#7b879e',
                    'backgroundColor' => 'rgba(123, 135, 158, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                    'pointBackgroundColor' => '#7b879e',
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
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
