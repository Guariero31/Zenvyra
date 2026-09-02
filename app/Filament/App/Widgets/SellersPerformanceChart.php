<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Models\Tenant;
use App\Services\SalesReportService;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class SellersPerformanceChart extends ChartWidget
{
    protected static ?int $sort = 8;

    protected ?string $heading = 'Ranking de vendedores';

    protected ?string $description = 'Faturamento bruto e comissão dos usuários com papel Vendedor nos últimos 12 meses.';

    protected int|string|array $columnSpan = 'full';

    public function getHeight(): string|int|null
    {
        return 260;
    }

    public static function canView(): bool
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Tenant
            && $tenant->gestaoSettings()->moduloVendedoresAtivo();
    }

    protected function getData(): array
    {
        $data = app(SalesReportService::class)->sellersPerformance();

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento bruto (R$)',
                    'data' => $data['gross'],
                    'backgroundColor' => 'rgb(59, 130, 246)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 0,
                    'borderRadius' => 4,
                    'hoverBackgroundColor' => 'rgb(96, 165, 250)',
                ],
                [
                    'label' => 'Comissão (R$)',
                    'data' => $data['commission'],
                    'backgroundColor' => 'rgb(245, 158, 11)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 0,
                    'borderRadius' => 4,
                    'hoverBackgroundColor' => 'rgb(251, 191, 36)',
                ],
            ],
            'labels' => $data['labels'],
        ];
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
                    'grid' => ['color' => 'rgba(255,255,255,0.04)'],
                    'ticks' => ['color' => 'rgba(255,255,255,0.45)'],
                ],
                'y' => [
                    'grid' => ['color' => 'rgba(255,255,255,0.04)'],
                    'ticks' => ['color' => 'rgba(255,255,255,0.45)'],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
