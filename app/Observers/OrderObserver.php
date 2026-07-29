<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Enums\TenantPortalRole;
use App\Mail\LowStockAlertMail;
use App\Mail\OrderPaidMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\OrderLifecycleManager;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    public function __construct(private OrderLifecycleManager $orders) {}

    public function created(Order $order): void
    {
        $this->orders->deductInventoryIfPaid($order);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('user_id')) {
            $this->orders->recalculateTotal($order);
        }

        if ($order->wasChanged('status')) {
            $this->orders->deductInventoryIfPaid($order);
            $this->onStatusChanged($order);
        }
    }

    /**
     * Reage às transições de status do pedido:
     *  - paid → envia confirmação ao cliente e verifica estoque crítico.
     */
    private function onStatusChanged(Order $order): void
    {
        if ($order->status !== OrderStatus::Paid) {
            return;
        }

        $order->loadMissing(['customer', 'items.product.variations', 'items.variation']);

        $this->sendOrderPaidEmail($order);
        $this->checkLowStockForItems($order);
    }

    /** Envia e-mail de confirmação ao cliente, se ele tiver e-mail cadastrado. */
    private function sendOrderPaidEmail(Order $order): void
    {
        $customer = $order->customer;

        if (! $customer || blank($customer->email)) {
            return;
        }

        $tenant    = TenantContext::current();
        $storeName = $tenant?->displayName() ?? config('app.name');

        $items = $order->items->map(fn (OrderItem $item): array => [
            'name'      => $item->product?->title ?? '—',
            'variation' => $item->variation?->name ?? null,
            'quantity'  => $item->quantity,
            'subtotal'  => bcmul((string) $item->quantity, (string) $item->price_at_sale, 2),
        ])->all();

        Mail::to($customer->email)->send(new OrderPaidMail(
            customerName:  $customer->name,
            orderId:       $order->getKey(),
            storeName:     $storeName,
            paymentMethod: $order->payment_method,
            items:         $items,
            totalAmount:   (string) $order->total_amount,
        ));
    }

    /**
     * Para cada item do pedido, verifica se o produto ficou com estoque crítico
     * e envia alerta para os administradores da loja.
     */
    private function checkLowStockForItems(Order $order): void
    {
        $tenant = TenantContext::current();

        if (! $tenant) {
            return;
        }

        $admins = $tenant->users()
            ->wherePivot('tu_papel', TenantPortalRole::AdministradorCliente->value)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        $storeName = $tenant->displayName();

        $order->items->each(function (OrderItem $item) use ($admins, $storeName): void {
            $product = $item->product;

            if (! $product || ! $product->low_stock_threshold) {
                return;
            }

            // Recarrega o saldo atualizado após a baixa
            $product->refresh();

            if (! $product->isLowStock()) {
                return;
            }

            $currentStock = $item->variation
                ? ($item->variation->fresh()?->stock ?? 0)
                : $product->stock;

            $admins->each(function (User $admin) use ($storeName, $product, $item, $currentStock): void {
                Mail::to($admin->email)->send(new LowStockAlertMail(
                    storeName:     $storeName,
                    productName:   $product->title,
                    variationName: $item->variation?->name,
                    currentStock:  (int) $currentStock,
                    threshold:     (int) $product->low_stock_threshold,
                ));
            });
        });
    }
}
