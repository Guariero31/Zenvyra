<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\ChartWidget;

class PaymentMethodRevenueChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'Formas de Pagamento';

    protected ?string $description = 'Participação de cada meio de pagamento (12 meses).';

    public function getHeight(): string|int|null
    {
        return 240;
    }

    protected function getData(): array
    {
        $data = app(SalesReportService::class)->revenueByPaymentMethod();

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento líquido (R$)',
                    'data' => $data['data'],
                    'backgroundColor' => [
                        'rgb(70, 72, 212)',    // Indigo — principal
                        'rgb(0, 110, 45)',     // Verde — secundário
                        'rgb(141, 75, 0)',     // Âmbar — terciário
                        'rgb(186, 26, 26)',    // Error red
                        'rgba(118, 117, 134, 0.75)', // Cinza — outros
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
                        'color' => '#464554',
                    ],
                ],
            ],
        ];
    }
}
