<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Faturamento mensal';

    protected ?string $description = 'Valor bruto e líquido vendido por mês.';

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
                    'backgroundColor' => 'rgba(99, 102, 241, 0.5)',
                    'borderColor' => 'rgb(99, 102, 241)',
                ],
                [
                    'label' => 'Faturamento líquido (R$)',
                    'data' => $series['net'],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
