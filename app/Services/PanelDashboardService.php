<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BillingPeriod;
use App\Enums\SubscriptionStatus;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

class PanelDashboardService
{
    /** @return array{stores: int, forecastCents: int, overdue: int, upcoming: int, evaluations: int, statuses: array<string, int>} */
    public function platformSummary(): array
    {
        $tenants = Tenant::query()->with('currentSubscription.plan')->get();
        $summary = ['stores' => 0, 'forecastCents' => 0, 'overdue' => 0, 'upcoming' => 0, 'evaluations' => 0, 'statuses' => []];

        foreach ($tenants as $tenant) {
            $subscription = $tenant->currentSubscription;
            if (! $subscription instanceof Subscription) {
                continue;
            }

            $status = $subscription->status->value;
            $summary['statuses'][$status] = ($summary['statuses'][$status] ?? 0) + 1;
            if ($tenant->te_ativo && $subscription->grantsAccess()) {
                $summary['stores']++;
            }
            if ($subscription->status === SubscriptionStatus::PastDue) {
                $summary['overdue']++;
            }
            if ($subscription->status->isLive() && $subscription->next_billing_at?->between(now(), now()->addDays(7))) {
                $summary['upcoming']++;
            }
            if ($subscription->isEvaluation() && $subscription->onTrial() && $subscription->trial_ends_at?->lte(now()->addDays(7))) {
                $summary['evaluations']++;
            }
            if ($tenant->te_ativo && $subscription->isActive() && ! $subscription->isEvaluation() && $subscription->plan !== null) {
                $priceCents = (int) str_replace('.', '', $subscription->plan->price);
                $months = match ($subscription->plan->billing_period) {
                    BillingPeriod::Quarterly => 3,
                    BillingPeriod::Yearly => 12,
                    default => 1,
                };
                $summary['forecastCents'] += intdiv($priceCents + intdiv($months, 2), $months);
            }
        }

        return $summary;
    }

    /** @return Builder<Product> */
    public function restockQuery(): Builder
    {
        return Product::query()->where('status', 'active')->where(function (Builder $query): void {
            $query->where(fn (Builder $simple) => $simple->where('has_variations', false)
                ->whereColumn('stock', '<=', 'low_stock_threshold'))
                ->orWhere(fn (Builder $variable) => $variable->where('has_variations', true)
                    ->whereHas('variations', fn (Builder $variation) => $variation->whereColumn('stock', '<=', 'low_stock_threshold')));
        });
    }

    public function formatCents(int $cents): string
    {
        return 'R$ '.number_format(intdiv($cents, 100), 0, ',', '.').','.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
