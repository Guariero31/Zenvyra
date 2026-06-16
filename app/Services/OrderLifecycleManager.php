<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderLifecycleManager
{
    public function __construct(
        private StockManager $stock,
        private CommissionCalculator $commission,
    ) {}

    public function recalculateTotal(Order $order): void
    {
        $order->loadMissing('seller');
        $items = $order->items()->with('product')->get();
        $total = '0.00';
        $commissionAmount = '0.00';

        foreach ($items as $item) {
            $total = bcadd($total, bcmul((string) $item->quantity, (string) $item->price_at_sale, 2), 2);

            $snapshot = $this->commission->snapshotForItem($item, $order);
            $commissionAmount = bcadd($commissionAmount, $snapshot['amount'], 2);

            $item->forceFill([
                'commission_rate' => $snapshot['rate'],
                'commission_amount' => $snapshot['amount'],
            ])->saveQuietly();
        }

        $order->forceFill([
            'total_amount' => $total,
            'commission_amount' => $commissionAmount,
        ])->saveQuietly();
    }

    public function deductInventoryIfPaid(Order $order): void
    {
        if ($order->status !== OrderStatus::Paid || filled($order->inventory_deducted_at)) {
            return;
        }

        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()
                ->where('id', $order->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder || $lockedOrder->status !== OrderStatus::Paid || filled($lockedOrder->inventory_deducted_at)) {
                return;
            }

            $lockedOrder->items()
                ->with(['variation', 'product'])
                ->get()
                ->each(fn (OrderItem $item) => $this->stock->registerSale($item));

            $lockedOrder->forceFill([
                'inventory_deducted_at' => now(),
            ])->saveQuietly();
        });
    }

    /**
     * Cancela um pedido. Permitido somente enquanto pendente: nesse estado o
     * estoque ainda não foi baixado, logo o cancelamento não movimenta estoque.
     * Pedidos pagos devem seguir pelo fluxo de estorno.
     */
    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()
                ->where('id', $order->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder) {
                throw ValidationException::withMessages([
                    'order' => 'Pedido não encontrado.',
                ]);
            }

            if (! $lockedOrder->status->isCancellable()) {
                throw ValidationException::withMessages([
                    'order' => 'Somente pedidos pendentes podem ser cancelados. Pedidos pagos devem ser estornados.',
                ]);
            }

            $lockedOrder->forceFill([
                'status' => OrderStatus::Canceled,
            ])->saveQuietly();

            $order->setRawAttributes($lockedOrder->getAttributes());
            $order->syncOriginal();
        });
    }
}
