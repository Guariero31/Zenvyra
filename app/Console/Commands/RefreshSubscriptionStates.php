<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\SubscriptionManager;
use Illuminate\Console\Command;

class RefreshSubscriptionStates extends Command
{
    protected $signature = 'subscriptions:refresh';

    protected $description = 'Reavalia assinaturas vivas por data: expira trials e ciclos vencidos após a tolerância';

    public function handle(SubscriptionManager $manager): int
    {
        $liveStatuses = array_map(
            fn (SubscriptionStatus $status): string => $status->value,
            array_filter(SubscriptionStatus::cases(), fn (SubscriptionStatus $status): bool => $status->isLive()),
        );

        $processed = 0;
        $backfilled = 0;

        Subscription::query()
            ->whereIn('status', $liveStatuses)
            ->each(function (Subscription $subscription) use ($manager, &$processed, &$backfilled): void {
                $wasMissingDates = $subscription->next_billing_at === null
                    || ($subscription->status === SubscriptionStatus::Active && $subscription->ends_at === null);

                $manager->ensureBillingDates($subscription);

                if ($wasMissingDates && $subscription->next_billing_at !== null) {
                    $backfilled++;
                }

                $previous = $subscription->status;
                $manager->refreshState($subscription);

                if ($subscription->status !== $previous) {
                    $processed++;
                }
            });

        $this->info("Assinaturas atualizadas: {$processed}.");
        $this->info("Datas de cobrança preenchidas: {$backfilled}.");

        return self::SUCCESS;
    }
}
