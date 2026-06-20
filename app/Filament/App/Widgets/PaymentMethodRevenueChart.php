<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class PaymentMethodRevenueChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'Faturamento por forma de pagamento';

    protected ?string $description = 'Participação de cada meio de pagamento (12 meses).';

    protected function getData(): array
    {
        $data = app(SalesReportService::class)->revenueByPaymentMethod();

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento líquido (R$)',
                    'data' => $data['data'],
                    'backgroundColor' => [
                        'rgb(99, 102, 241)',
                        'rgb(16, 185, 129)',
                        'rgb(249, 115, 22)',
                        'rgb(236, 72, 153)',
                        'rgb(148, 163, 184)',
                    ],
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
