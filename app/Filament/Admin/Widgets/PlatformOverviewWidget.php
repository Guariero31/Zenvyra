<?php

declare(strict_types=1);

namespace App\Filament\Admin\Widgets;

use App\Services\PanelDashboardService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformOverviewWidget extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected int|array|null $columns = ['default' => 1, 'sm' => 2, 'xl' => 4];

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $service = app(PanelDashboardService::class);
        $summary = $service->platformSummary();

        return [
            Stat::make('Lojas com acesso', $summary['stores'])->icon('heroicon-o-building-storefront')->description('Acesso liberado pela assinatura'),
            Stat::make('Receita recebida', '—')->icon('heroicon-o-chart-bar')->description('Recebimentos ainda não registrados'),
            Stat::make('Receita prevista', $service->formatCents($summary['forecastCents']))->icon('heroicon-o-arrow-trending-up')->description('Valor mensal dos planos ativos'),
            Stat::make('Assinaturas em atraso', $summary['overdue'])->icon('heroicon-o-clock')->description($summary['overdue'] ? 'Precisam de atenção' : 'Nenhuma assinatura em atraso')->color($summary['overdue'] ? 'warning' : 'success'),
        ];
    }
}
