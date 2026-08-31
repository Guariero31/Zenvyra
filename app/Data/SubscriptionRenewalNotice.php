<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Carbon;

readonly class SubscriptionRenewalNotice
{
    public function __construct(
        public string $title,
        public string $message,
        public string $severity,
        public int $daysRemaining,
        public Carbon $expiresAt,
        public ?int $graceDaysAfterExpiry,
    ) {}
}
