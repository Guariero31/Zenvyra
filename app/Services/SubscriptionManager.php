<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Centraliza as transições de estado de uma assinatura. Hoje (Fase 1) é acionado
 * manualmente pelo painel master e pelo comando agendado; na Fase 3 o webhook do
 * gateway de pagamento passa a alimentar os mesmos métodos, sem reescrever a
 * lógica de liberação de acesso.
 */
class SubscriptionManager
{
    /**
     * Cria uma assinatura iniciando o período de teste do plano. Se o plano não
     * tiver trial, a assinatura já nasce ativa.
     */
    public function startSubscription(Tenant $tenant, Plan $plan): Subscription
    {
        $now = Carbon::now();

        if ($plan->hasTrial()) {
            return $tenant->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::Trialing,
                'starts_at' => $now,
                'trial_ends_at' => $now->copy()->addDays($plan->trial_days),
            ]);
        }

        $subscription = $tenant->subscriptions()->make(['plan_id' => $plan->id]);

        return $this->activate($subscription);
    }

    /**
     * Marca a assinatura como ativa e fecha o ciclo em 30 dias (ends_at recebe
     * o mesmo valor da próxima cobrança). Assim, ao chegar a data sem renovação,
     * o refreshState() leva a assinatura para atrasada e, depois da tolerância,
     * para expirada — sem intervenção manual. Usado na contratação e na
     * confirmação de pagamento.
     */
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
        ])->save();

        return $subscription;
    }

    /**
     * Próxima cobrança: sempre a data informada (ou hoje) somada ao ciclo fixo
     * de cobrança. Fonte única usada pela ativação e pelo formulário do painel.
     */
    public function nextBillingDate(?CarbonInterface $from = null): CarbonInterface
    {
        return ($from?->copy() ?? Carbon::now())->addDays($this->billingCycleDays());
    }

    private function billingCycleDays(): int
    {
        return (int) config('subscriptions.billing_cycle_days', 30);
    }

    /**
     * Marca a assinatura como atrasada e abre o período de tolerância a partir
     * da data informada (vencimento do trial ou do ciclo pago).
     */
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

    /**
     * Reavalia o estado de uma assinatura com base apenas na passagem do tempo.
     * É a engrenagem que faz trial vencido e ciclo atrasado caminharem sozinhos
     * em direção ao bloqueio, mesmo no fluxo manual.
     */
    public function refreshState(Subscription $subscription): Subscription
    {
        $now = Carbon::now();

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

    private function graceDays(): int
    {
        return (int) config('subscriptions.grace_days', 5);
    }
}
