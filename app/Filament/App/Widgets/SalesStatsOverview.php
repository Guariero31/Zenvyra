<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Services\SalesReportService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStatsOverview extends BaseWidget
{
    protected static ?int $sort = 3;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $kpis = app(SalesReportService::class)->currentMonthKpis();

        return [
            Stat::make('Faturamento líquido (mês)', $this->money($kpis['net']))
                ->description($this->netDescription($kpis['netDeltaPercent']))
                ->descriptionIcon($this->trendIcon($kpis['netDeltaPercent']))
                ->color($this->trendColor($kpis['netDeltaPercent'])),
            Stat::make('Vendas no mês', (string) $kpis['salesCount'])
                ->description($this->countDescription($kpis['countDelta']))
                ->descriptionIcon($this->trendIcon($kpis['countDelta']))
                ->color($this->trendColor($kpis['countDelta'])),
            Stat::make('Ticket médio', $this->money($kpis['averageTicket']))
                ->description('Faturamento líquido por venda')
                ->color('info'),
            Stat::make('Comissões no mês', $this->money($kpis['commission']))
                ->description('Valor bruto comissionado')
                ->color('warning'),
            Stat::make('Estornos no mês', $this->money($kpis['refunds']))
                ->description('Valor devolvido aos clientes')
                ->color($kpis['refunds'] > 0 ? 'danger' : 'gray'),
        ];
    }

    private function money(float $value): string
    {
        return 'R$ '.number_format($value, 2, ',', '.');
    }

    private function netDescription(?float $delta): string
    {
        if ($delta === null) {
            return 'Sem base do mês anterior';
        }

        return number_format(abs($delta), 1, ',', '.').'% vs. mês anterior';
    }

    private function countDescription(int $delta): string
    {
        return match (true) {
            $delta > 0 => "+{$delta} vs. mês anterior",
            $delta < 0 => "{$delta} vs. mês anterior",
            default => 'Estável vs. mês anterior',
        };
    }

    private function trendIcon(int|float|null $delta): ?string
    {
        return match (true) {
            $delta === null => null,
            $delta > 0 => 'heroicon-m-arrow-trending-up',
            $delta < 0 => 'heroicon-m-arrow-trending-down',
            default => 'heroicon-m-minus-small',
        };
    }

    private function trendColor(int|float|null $delta): string
    {
        return match (true) {
            $delta === null => 'gray',
            $delta > 0 => 'success',
            $delta < 0 => 'danger',
            default => 'gray',
        };
    }
}
