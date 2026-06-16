<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class TopProductsChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Top produtos por receita';

    protected ?string $description = 'Cinco produtos que mais faturaram (12 meses).';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = app(SalesReportService::class)->topProductsByRevenue();

        return [
            'datasets' => [
                [
                    'label' => 'Receita (R$)',
                    'data' => $data['data'],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
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
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
