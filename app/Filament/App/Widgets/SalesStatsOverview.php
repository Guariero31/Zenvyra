<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Services\SalesReportService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|array|null $columns = 4;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $kpis = app(SalesReportService::class)->currentMonthKpis();

        $pendingCount = Order::query()
            ->where('status', OrderStatus::Pending)
            ->count();

        $lowStockCount = Product::query()
            ->where('status', 'active')
            ->whereRaw('stock <= low_stock_threshold AND low_stock_threshold > 0')
            ->count();

        return [
            Stat::make('Vendas do mês', $this->money($kpis['net']))
                ->description($this->netDescription($kpis['netDeltaPercent']))
                ->descriptionIcon($this->trendIcon($kpis['netDeltaPercent']))
                ->icon('heroicon-o-currency-dollar')
                ->color($this->trendColor($kpis['netDeltaPercent'])),

            Stat::make('Ticket médio', $this->money($kpis['averageTicket']))
                ->description('Faturamento líquido por venda')
                ->icon('heroicon-o-receipt-percent')
                ->color('primary'),

            Stat::make('Pedidos pendentes', (string) $pendingCount)
                ->description($pendingCount > 0 ? 'Requer atenção' : 'Nenhum pendente')
                ->descriptionIcon($pendingCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->icon('heroicon-o-clipboard-document-list')
                ->color($pendingCount > 0 ? 'warning' : 'success'),

            Stat::make('Estoque baixo', $lowStockCount.' '.($lowStockCount === 1 ? 'item' : 'itens'))
                ->description($lowStockCount > 0 ? 'Reposição urgente' : 'Estoque saudável')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-circle' : 'heroicon-m-check-circle')
                ->icon('heroicon-o-archive-box')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),
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
