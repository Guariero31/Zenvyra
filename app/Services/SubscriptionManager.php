<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubscriptionIntent;
use App\Enums\SubscriptionStatus;
use App\Enums\TenantPortalRole;
use App\Mail\SubscriptionExpiredMail;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Centraliza as transições de estado de uma assinatura. Hoje (Fase 1) é acionado
 * manualmente pelo painel master e pelo comando agendado; na Fase 3 o webhook do
 * gateway de pagamento passa a alimentar os mesmos métodos, sem reescrever a
 * lógica de liberação de acesso.
 */
class SubscriptionManager
{
    public function startEvaluation(Tenant $tenant, Plan $plan, ?int $days = null): Subscription
    {
        $now = Carbon::now();
        $evaluationDays = $days ?? (int) config('subscriptions.evaluation_days', 20);
        $trialEnds = $now->copy()->addDays($evaluationDays);

        return $tenant->subscriptions()->create([
            'plan_id' => $plan->id,
            'intent' => SubscriptionIntent::Evaluation,
            'status' => SubscriptionStatus::Trialing,
            'starts_at' => $now,
            'trial_ends_at' => $trialEnds,
            'next_billing_at' => $trialEnds,
        ]);
    }

    /**
     * Inicia assinatura comercial para loja que já fechou negócio. Ignora trial
     * do plano e ativa o ciclo de cobrança imediatamente.
     */
    public function startCommercialSubscription(Tenant $tenant, Plan $plan): Subscription
    {
        $subscription = $tenant->subscriptions()->make([
            'plan_id' => $plan->id,
            'intent' => SubscriptionIntent::Commercial,
        ]);

        return $this->activate($subscription);
    }

    /**
     * Cria uma assinatura iniciando o período de teste do plano. Se o plano não
     * tiver trial, a assinatura já nasce ativa.
     */
    public function startSubscription(Tenant $tenant, Plan $plan): Subscription
    {
        $now = Carbon::now();

        if ($plan->hasTrial()) {
            $trialEnds = $now->copy()->addDays($plan->trial_days);

            return $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'intent' => SubscriptionIntent::Commercial,
                'status' => SubscriptionStatus::Trialing,
                'starts_at' => $now,
                'trial_ends_at' => $trialEnds,
                'next_billing_at' => $trialEnds,
            ]);
        }

        $subscription = $tenant->subscriptions()->make([
            'plan_id' => $plan->id,
            'intent' => SubscriptionIntent::Commercial,
        ]);

        return $this->activate($subscription);
    }

    public function convertEvaluationToCommercial(Subscription $subscription, Plan $plan): Subscription
    {
        $subscription->fill([
            'plan_id' => $plan->id,
            'intent' => SubscriptionIntent::Commercial,
        ]);

        $subscription->tenant?->update([
            'te_data_retention_ends_at' => null,
            'te_data_purged_at' => null,
            'te_ativo' => true,
        ]);

        return $this->activate($subscription);
    }

    public function activate(Subscription $subscription): Subscription
    {
        $now = Carbon::now();
        $nextBilling = $this->nextBillingDate($now);

        $subscription->fill([
            'status' => SubscriptionStatus::Active,
            'starts_at' => $subscription->starts_at ?? $now,
            'ends_at' => $nextBilling,
            'grace_ends_at' => null,
            'canceled_at' => null,
            'next_billing_at' => $nextBilling,
            'trial_ends_at' => null,
        ])->save();

        return $subscription;
    }

    public function nextBillingDate(?CarbonInterface $from = null): CarbonInterface
    {
        return ($from?->copy() ?? Carbon::now())->addDays($this->billingCycleDays());
    }

    private function billingCycleDays(): int
    {
        return (int) config('subscriptions.billing_cycle_days', 30);
    }

    public function markPastDue(Subscription $subscription, ?Carbon $since = null): Subscription
    {
        $reference = $since ?? Carbon::now();

        $subscription->fill([
            'status' => SubscriptionStatus::PastDue,
            'grace_ends_at' => $reference->copy()->addDays($this->graceDays()),
        ])->save();

        return $subscription;
    }

    public function expire(Subscription $subscription): Subscription
    {
        $subscription->fill([
            'status' => SubscriptionStatus::Expired,
            'ends_at' => $subscription->ends_at ?? Carbon::now(),
        ])->save();

        if ($subscription->intent === SubscriptionIntent::Evaluation) {
            $this->scheduleEvaluationDataRetention($subscription);
        }

        $this->notifySubscriptionExpired($subscription);

        return $subscription;
    }

    public function cancel(Subscription $subscription): Subscription
    {
        $subscription->fill([
            'status' => SubscriptionStatus::Canceled,
            'canceled_at' => Carbon::now(),
        ])->save();

        return $subscription;
    }

    public function refreshState(Subscription $subscription): Subscription
    {
        $this->ensureBillingDates($subscription);

        $now = Carbon::now();

        if ($subscription->intent === SubscriptionIntent::Evaluation
            && $subscription->status === SubscriptionStatus::Trialing
            && $subscription->trial_ends_at !== null
            && $subscription->trial_ends_at->isPast()) {
            return $this->expire($subscription);
        }

        if ($subscription->status === SubscriptionStatus::Trialing
            && $subscription->trial_ends_at !== null
            && $subscription->trial_ends_at->isPast()) {
            return $this->markPastDue($subscription, $subscription->trial_ends_at);
        }

        if ($subscription->status === SubscriptionStatus::Active
            && $subscription->ends_at !== null
            && $subscription->ends_at->isPast()) {
            return $this->markPastDue($subscription, $subscription->ends_at);
        }

        if ($subscription->status === SubscriptionStatus::PastDue
            && ($subscription->grace_ends_at === null || $subscription->grace_ends_at->lte($now))) {
            return $this->expire($subscription);
        }

        return $subscription;
    }

    public function ensureBillingDates(Subscription $subscription): Subscription
    {
        if ($subscription->status === SubscriptionStatus::Trialing && $subscription->trial_ends_at !== null) {
            if ($subscription->next_billing_at === null) {
                $subscription->next_billing_at = $subscription->trial_ends_at;
            }
        }

        if ($subscription->status === SubscriptionStatus::Active) {
            $start = $subscription->starts_at ?? Carbon::now();

            if ($subscription->ends_at === null) {
                $subscription->ends_at = $this->nextBillingDate($start);
            }

            if ($subscription->next_billing_at === null) {
                $subscription->next_billing_at = $subscription->ends_at;
            }
        }

        if ($subscription->isDirty()) {
            $subscription->save();
        }

        return $subscription;
    }

    /** Notifica os administradores da loja quando a assinatura expira. */
    private function notifySubscriptionExpired(Subscription $subscription): void
    {
        $tenant = $subscription->tenant;
        $plan   = $subscription->plan;

        if (! $tenant instanceof Tenant || ! $plan) {
            return;
        }

        $expiredAt = ($subscription->ends_at ?? Carbon::now())->format('d/m/Y');

        $tenant->users()
            ->wherePivot('tu_papel', TenantPortalRole::AdministradorCliente->value)
            ->get()
            ->each(function (User $admin) use ($tenant, $plan, $expiredAt): void {
                Mail::to($admin->email)->send(new SubscriptionExpiredMail(
                    storeName:  $tenant->displayName(),
                    planName:   $plan->name,
                    expiredAt:  $expiredAt,
                ));
            });
    }

    private function scheduleEvaluationDataRetention(Subscription $subscription): void
    {
        $tenant = $subscription->tenant;

        if (! $tenant instanceof Tenant || $tenant->te_data_purged_at !== null) {
            return;
        }

        $retentionDays = (int) config('subscriptions.evaluation_data_retention_days', 60);

        $tenant->update([
            'te_data_retention_ends_at' => now()->addDays($retentionDays),
        ]);
    }

    private function graceDays(): int
    {
        return (int) config('subscriptions.grace_days', 5);
    }
}
