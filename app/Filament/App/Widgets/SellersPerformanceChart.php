<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class SellersPerformanceChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'Ranking de vendedores';

    protected ?string $description = 'Faturamento bruto e comissão gerada nos últimos 12 meses.';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = app(SalesReportService::class)->sellersPerformance();

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento bruto (R$)',
                    'data' => $data['gross'],
                    'backgroundColor' => 'rgba(99, 102, 241, 0.5)',
                    'borderColor' => 'rgb(99, 102, 241)',
                ],
                [
                    'label' => 'Comissão (R$)',
                    'data' => $data['commission'],
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                    'borderColor' => 'rgb(245, 158, 11)',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
