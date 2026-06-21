<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class TenantOrderPurgeService
{
    public function purge(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $order->load(['refunds.items', 'items']);

            foreach ($order->refunds as $refund) {
                $refund->items()->delete();
            }

            $order->refunds()->delete();
            DB::table('stock_movements')->where('order_id', $order->getKey())->delete();
            $order->items()->delete();
            $order->delete();
        });
    }
}
