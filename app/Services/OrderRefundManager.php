<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderRefund;
use App\Models\OrderRefundItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderRefundManager
{
    public function __construct(private StockManager $stock) {}

    /**
     * @param  array<int, int>  $items  Mapa orderItemId => quantity
     */
    public function refund(Order $order, array $items, string $reason, bool $restock, ?int $userId): OrderRefund
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'O motivo do estorno é obrigatório.',
            ]);
        }

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Selecione ao menos um item para estornar.',
            ]);
        }

        return DB::transaction(function () use ($order, $items, $reason, $restock, $userId): OrderRefund {
            $lockedOrder = Order::query()
                ->where('id', $order->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder) {
                throw ValidationException::withMessages([
                    'order' => 'Pedido não encontrado.',
                ]);
            }

            if (! $lockedOrder->status->isRefundable()) {
                throw ValidationException::withMessages([
                    'order' => 'Somente pedidos pagos ou parcialmente estornados podem receber estorno.',
                ]);
            }

            $orderItems = $lockedOrder->items()
                ->with(['variation', 'product'])
                ->get()
                ->keyBy('id');

            $refundLines = [];
            $totalRefundAmount = '0.00';

            foreach ($items as $orderItemId => $quantity) {
                $quantity = (int) $quantity;

                if ($quantity <= 0) {
                    continue;
                }

                /** @var OrderItem|null $orderItem */
                $orderItem = $orderItems->get($orderItemId);

                if (! $orderItem) {
                    throw ValidationException::withMessages([
                        "items.{$orderItemId}" => 'Item do pedido inválido.',
                    ]);
                }

                $refundableQuantity = $orderItem->refundableQuantity();

                if ($quantity > $refundableQuantity) {
                    throw ValidationException::withMessages([
                        "items.{$orderItemId}" => "Quantidade máxima estornável: {$refundableQuantity}.",
                    ]);
                }

                $lineAmount = bcmul((string) $quantity, (string) $orderItem->price_at_sale, 2);
                $totalRefundAmount = bcadd($totalRefundAmount, $lineAmount, 2);

                $refundLines[] = [
                    'order_item' => $orderItem,
                    'quantity' => $quantity,
                    'amount' => $lineAmount,
                ];
            }

            if ($refundLines === []) {
                throw ValidationException::withMessages([
                    'items' => 'Informe uma quantidade válida para ao menos um item.',
                ]);
            }

            $orderRefund = OrderRefund::create([
                'order_id' => $lockedOrder->id,
                'amount' => $totalRefundAmount,
                'reason' => $reason,
                'refunded_by' => $userId,
                'restocked' => $restock,
            ]);

            foreach ($refundLines as $line) {
                OrderRefundItem::create([
                    'order_refund_id' => $orderRefund->id,
                    'order_item_id' => $line['order_item']->id,
                    'quantity' => $line['quantity'],
                    'amount' => $line['amount'],
                ]);

                if ($restock) {
                    $this->stock->registerRefund($line['order_item'], $line['quantity'], $userId);
                }
            }

            $newRefundedAmount = bcadd((string) $lockedOrder->refunded_amount, $totalRefundAmount, 2);
            $isFullyRefunded = bccomp($newRefundedAmount, (string) $lockedOrder->total_amount, 2) >= 0;

            $lockedOrder->forceFill([
                'refunded_amount' => $newRefundedAmount,
                'status' => $isFullyRefunded ? OrderStatus::Refunded : OrderStatus::PartiallyRefunded,
                'refunded_at' => $isFullyRefunded ? now() : $lockedOrder->refunded_at,
            ])->saveQuietly();

            return $orderRefund->load('items.orderItem.product');
        });
    }

    /**
     * @return array<int, int>
     */
    public function refundableItemsMap(Order $order): array
    {
        return $order->items()
            ->get()
            ->filter(fn (OrderItem $item): bool => $item->refundableQuantity() > 0)
            ->mapWithKeys(fn (OrderItem $item): array => [$item->id => $item->refundableQuantity()])
            ->all();
    }
}
