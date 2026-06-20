<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DashboardOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $todayRevenue = Order::whereIn('status', OrderStatus::soldValues())
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');

        $pendingOrders = Order::where('status', OrderStatus::Pending)
            ->count();

        $activeProducts = Product::where('status', 'active')
            ->count();

        $newCustomersThisMonth = Customer::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $newCustomersLastMonth = Customer::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        $customerDelta = $newCustomersThisMonth - $newCustomersLastMonth;

        return [
            Stat::make('Vendas hoje', 'R$ '.number_format((float) $todayRevenue, 2, ',', '.'))
                ->description('Faturamento líquido do dia')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Pedidos pendentes', (string) $pendingOrders)
                ->description($pendingOrders > 0 ? 'Aguardando confirmação' : 'Nenhum pendente')
                ->descriptionIcon($pendingOrders > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),

            Stat::make('Produtos ativos', (string) $activeProducts)
                ->description('Produtos visíveis no catálogo')
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),

            Stat::make('Novos clientes (mês)', (string) $newCustomersThisMonth)
                ->description($this->customerDeltaDescription($customerDelta))
                ->descriptionIcon($this->customerDeltaIcon($customerDelta))
                ->color($customerDelta >= 0 ? 'success' : 'danger'),
        ];
    }

    private function customerDeltaDescription(int $delta): string
    {
        return match (true) {
            $delta > 0 => "+{$delta} vs. mês anterior",
            $delta < 0 => "{$delta} vs. mês anterior",
            default => 'Estável vs. mês anterior',
        };
    }

    private function customerDeltaIcon(int $delta): string
    {
        return match (true) {
            $delta > 0 => 'heroicon-m-arrow-trending-up',
            $delta < 0 => 'heroicon-m-arrow-trending-down',
            default => 'heroicon-m-minus-small',
        };
    }
}
