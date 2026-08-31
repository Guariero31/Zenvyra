<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\TenantPortalRole;
use App\Models\Order;
use App\Models\Tenant;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

/**
 * Agregações de vendas para o dashboard de BI do lojista. Todas as consultas
 * rodam sobre a conexão atual, então respeitam o search_path do tenant ativo.
 *
 * A agregação mensal é feita em PHP (um SUM/COUNT por mês) para manter
 * compatibilidade tanto com PostgreSQL (produção) quanto com SQLite (testes),
 * evitando funções de data específicas de cada banco.
 */
class SalesReportService
{
    private const MONTH_ABBR = [
        1 => 'jan', 2 => 'fev', 3 => 'mar', 4 => 'abr',
        5 => 'mai', 6 => 'jun', 7 => 'jul', 8 => 'ago',
        9 => 'set', 10 => 'out', 11 => 'nov', 12 => 'dez',
    ];

    /**
     * Indicadores do mês corrente com comparação ao mês anterior.
     *
     * @return array{gross: float, refunds: float, net: float, commission: float, salesCount: int, averageTicket: float, netDeltaPercent: ?float, countDelta: int}
     */
    public function currentMonthKpis(): array
    {
        $now = CarbonImmutable::now();
        $current = $this->aggregateBetween($now->startOfMonth(), $now->endOfMonth());
        $previousMonth = $now->subMonth();
        $previous = $this->aggregateBetween($previousMonth->startOfMonth(), $previousMonth->endOfMonth());

        $net = $current['gross'] - $current['refunds'];
        $previousNet = $previous['gross'] - $previous['refunds'];

        return [
            'gross' => $current['gross'],
            'refunds' => $current['refunds'],
            'net' => $net,
            'commission' => $current['commission'],
            'salesCount' => $current['count'],
            'averageTicket' => $current['count'] > 0 ? $net / $current['count'] : 0.0,
            'netDeltaPercent' => $previousNet > 0 ? (($net - $previousNet) / $previousNet) * 100 : null,
            'countDelta' => $current['count'] - $previous['count'],
        ];
    }

    /**
     * Série mensal de faturamento (bruto e líquido) e contagem de vendas.
     *
     * @return array{labels: array<int, string>, gross: array<int, float>, net: array<int, float>, count: array<int, int>}
     */
    public function monthlySeries(int $months = 12): array
    {
        $labels = [];
        $gross = [];
        $net = [];
        $count = [];

        $cursor = CarbonImmutable::now()->startOfMonth()->subMonths($months - 1);

        for ($i = 0; $i < $months; $i++) {
            $month = $cursor->addMonths($i);
            $row = $this->aggregateBetween($month->startOfMonth(), $month->endOfMonth());

            $labels[] = self::MONTH_ABBR[$month->month].'/'.$month->format('y');
            $gross[] = $row['gross'];
            $net[] = $row['gross'] - $row['refunds'];
            $count[] = $row['count'];
        }

        return [
            'labels' => $labels,
            'gross' => $gross,
            'net' => $net,
            'count' => $count,
        ];
    }

    /**
     * Faturamento líquido por forma de pagamento nos últimos N meses.
     *
     * @return array{labels: array<int, string>, data: array<int, float>}
     */
    public function revenueByPaymentMethod(int $months = 12): array
    {
        $since = CarbonImmutable::now()->startOfMonth()->subMonths($months - 1);

        $rows = Order::query()
            ->whereIn('status', OrderStatus::soldValues())
            ->where('created_at', '>=', $since)
            ->selectRaw('payment_method, COALESCE(SUM(total_amount - refunded_amount), 0) as net')
            ->groupBy('payment_method')
            ->get();

        $labels = [];
        $data = [];

        foreach ($rows as $row) {
            $labels[] = $this->paymentMethodLabel($row->payment_method);
            $data[] = (float) $row->net;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Produtos com maior receita nos últimos N meses.
     *
     * @return array{labels: array<int, string>, data: array<int, float>}
     */
    public function topProductsByRevenue(int $limit = 5, int $months = 12): array
    {
        $since = CarbonImmutable::now()->startOfMonth()->subMonths($months - 1);

        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereIn('orders.status', OrderStatus::soldValues())
            ->where('orders.created_at', '>=', $since)
            ->selectRaw('products.title as title, COALESCE(SUM(order_items.quantity * order_items.price_at_sale), 0) as revenue')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->pluck('title')->all(),
            'data' => $rows->pluck('revenue')->map(fn ($value): float => (float) $value)->all(),
        ];
    }

    /**
     * Ranking de vendedores (papel Vendedor na loja) por faturamento bruto e comissão.
     *
     * @return array{labels: array<int, string>, gross: array<int, float>, commission: array<int, float>, salesCount: array<int, int>}
     */
    public function sellersPerformance(int $limit = 5, int $months = 12, ?Tenant $tenant = null): array
    {
        $tenant ??= Filament::getTenant();

        if (! $tenant instanceof Tenant) {
            return [
                'labels' => [],
                'gross' => [],
                'commission' => [],
                'salesCount' => [],
            ];
        }

        $since = CarbonImmutable::now()->startOfMonth()->subMonths($months - 1);

        $vinculosTable = DB::connection()->getDriverName() === 'pgsql'
            ? 'public.tu_vinculos'
            : 'tu_vinculos';

        $usersTable = DB::connection()->getDriverName() === 'pgsql'
            ? 'public.users'
            : 'users';

        $rows = DB::table($vinculosTable.' as tu_vinculos')
            ->join($usersTable.' as users', 'users.id', '=', 'tu_vinculos.user_id')
            ->leftJoin('orders', function ($join) use ($since): void {
                $join->on('orders.user_id', '=', 'users.id')
                    ->whereIn('orders.status', OrderStatus::soldValues())
                    ->where('orders.created_at', '>=', $since);
            })
            ->where('tu_vinculos.tenant_id', $tenant->getKey())
            ->where('tu_vinculos.tu_papel', TenantPortalRole::Vendedor->value)
            ->selectRaw('users.name as seller_name, COALESCE(SUM(orders.total_amount), 0) as gross, COALESCE(SUM(orders.commission_amount), 0) as commission, COUNT(orders.id) as sales_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('gross')
            ->orderBy('users.name')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->pluck('seller_name')->all(),
            'gross' => $rows->pluck('gross')->map(fn ($value): float => (float) $value)->all(),
            'commission' => $rows->pluck('commission')->map(fn ($value): float => (float) $value)->all(),
            'salesCount' => $rows->pluck('sales_count')->map(fn ($value): int => (int) $value)->all(),
        ];
    }

    /**
     * @return array{gross: float, refunds: float, commission: float, count: int}
     */
    private function aggregateBetween(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $row = Order::query()
            ->whereIn('status', OrderStatus::soldValues())
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(total_amount), 0) as gross, COALESCE(SUM(refunded_amount), 0) as refunds, COALESCE(SUM(commission_amount), 0) as commission, COUNT(*) as cnt')
            ->first();

        return [
            'gross' => (float) ($row->gross ?? 0),
            'refunds' => (float) ($row->refunds ?? 0),
            'commission' => (float) ($row->commission ?? 0),
            'count' => (int) ($row->cnt ?? 0),
        ];
    }

    private function paymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'dinheiro' => 'Dinheiro',
            'pix' => 'Pix',
            'cartao_credito' => 'Crédito',
            'cartao_debito' => 'Débito',
            default => 'Não informado',
        };
    }
}
