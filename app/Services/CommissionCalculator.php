<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;

class CommissionCalculator
{
    public function __construct(private readonly UserCommissionResolver $userCommission) {}

    public function effectiveRate(OrderItem $item, Order $order): string
    {
        $productRate = $item->product?->commission_rate;

        if ($productRate !== null) {
            return (string) $productRate;
        }

        return $this->userCommission->defaultRateForOrder($order);
    }

    /**
     * @return array{rate: string, amount: string}
     */
    public function snapshotForItem(OrderItem $item, Order $order): array
    {
        $rate = $this->effectiveRate($item, $order);
        $gross = bcmul((string) $item->quantity, (string) $item->price_at_sale, 2);
        $amount = bcdiv(bcmul($gross, $rate, 4), '100', 2);

        return [
            'rate' => $rate,
            'amount' => $amount,
        ];
    }
}
