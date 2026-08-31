<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\OrderItem;
use App\Services\OrderLifecycleManager;

class OrderItemObserver
{
    public function __construct(private OrderLifecycleManager $orders) {}

    public function created(OrderItem $orderItem): void
    {
        $this->orders->recalculateTotal($orderItem->order);
    }

    public function updated(OrderItem $orderItem): void
    {
        $this->orders->recalculateTotal($orderItem->order);
    }

    public function deleted(OrderItem $orderItem): void
    {
        $this->orders->recalculateTotal($orderItem->order);
    }
}
