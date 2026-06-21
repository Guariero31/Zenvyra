<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class PaymentMethodRevenueChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'Segmentos por forma de pagamento';

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
                        'rgba(148, 163, 184, 0.75)',  // Cinza — sem info
                        'rgb(59, 130, 246)',           // Azul — Pix / principal
                        'rgb(34, 197, 94)',            // Verde
                        'rgb(245, 158, 11)',           // Âmbar
                        'rgb(139, 92, 246)',           // Roxo
                    ],
                    'borderWidth' => 0,
                    'hoverOffset' => 6,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'cutout' => '65%',
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 18,
                        'color' => 'rgba(255,255,255,0.6)',
                    ],
                ],
            ],
        ];
    }
}
