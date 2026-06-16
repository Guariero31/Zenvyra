<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class MonthlySalesCountChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Número de vendas por mês';

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
                    'label' => 'Vendas',
                    'data' => $series['count'],
                    'backgroundColor' => 'rgba(99, 102, 241, 0.2)',
                    'borderColor' => 'rgb(99, 102, 241)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
