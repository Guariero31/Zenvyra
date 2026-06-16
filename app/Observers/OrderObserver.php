<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use App\Services\OrderLifecycleManager;

class OrderObserver
{
    public function __construct(private OrderLifecycleManager $orders) {}

    public function created(Order $order): void
    {
        $this->orders->deductInventoryIfPaid($order);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('seller_id')) {
            $this->orders->recalculateTotal($order);
        }

        if ($order->wasChanged('status')) {
            $this->orders->deductInventoryIfPaid($order);
        }
    }
}
