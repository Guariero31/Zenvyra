<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class TopProductsChart extends ChartWidget
{
    protected static ?int $sort = 7;

    protected ?string $heading = 'Top produtos por receita';

    protected ?string $description = 'Cinco produtos que mais faturaram nos últimos 12 meses.';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = app(SalesReportService::class)->topProductsByRevenue();

        return [
            'datasets' => [
                [
                    'label' => 'Receita (R$)',
                    'data' => $data['data'],
                    'backgroundColor' => 'rgb(59, 130, 246)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 0,
                    'borderRadius' => 4,
                    'hoverBackgroundColor' => 'rgb(96, 165, 250)',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['color' => 'rgba(255,255,255,0.04)'],
                    'ticks' => ['color' => 'rgba(255,255,255,0.45)'],
                    'beginAtZero' => true,
                ],
                'y' => [
                    'grid' => ['color' => 'rgba(255,255,255,0.04)'],
                    'ticks' => ['color' => 'rgba(255,255,255,0.45)'],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
