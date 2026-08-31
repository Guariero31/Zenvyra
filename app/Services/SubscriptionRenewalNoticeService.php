<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\SubscriptionRenewalNotice;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Carbon;

class SubscriptionRenewalNoticeService
{
    public function forTenant(Tenant $tenant): ?SubscriptionRenewalNotice
    {
        $subscription = $tenant->activeSubscription();

        if (! $subscription instanceof Subscription) {
            return null;
        }

        if ($subscription->onGracePeriod()) {
            return $this->gracePeriodNotice($subscription);
        }

        if ($subscription->onTrial()) {
            return $this->approachingTrialEndNotice($subscription);
        }

        if ($subscription->isActive()) {
            return $this->approachingCycleEndNotice($subscription);
        }

        return null;
    }

    private function gracePeriodNotice(Subscription $subscription): SubscriptionRenewalNotice
    {
        $expiresAt = $subscription->grace_ends_at ?? now();
        $daysRemaining = $this->daysUntil($expiresAt);

        return new SubscriptionRenewalNotice(
            title: 'Assinatura em tolerância',
            message: sprintf(
                'Sua assinatura venceu e o painel permanece acessível por tolerância. Restam %s (até %s). Regularize o pagamento para evitar o bloqueio.',
                $this->formatDaysRemaining($daysRemaining),
                $expiresAt->format('d/m/Y'),
            ),
            severity: $daysRemaining <= 2 ? 'danger' : 'warning',
            daysRemaining: $daysRemaining,
            expiresAt: $expiresAt,
            graceDaysAfterExpiry: null,
        );
    }

    private function approachingTrialEndNotice(Subscription $subscription): ?SubscriptionRenewalNotice
    {
        if ($subscription->trial_ends_at === null) {
            return null;
        }

        $daysRemaining = $this->daysUntil($subscription->trial_ends_at);

        if ($daysRemaining > $this->noticeDays()) {
            return null;
        }

        if ($subscription->isEvaluation()) {
            return new SubscriptionRenewalNotice(
                title: 'Avaliação terminando em breve',
                message: sprintf(
                    'Sua avaliação termina %s (%s). Após essa data, o acesso ao painel será bloqueado imediatamente. Entre em contato para contratar um plano.',
                    $this->formatDaysRemaining($daysRemaining),
                    $subscription->trial_ends_at->format('d/m/Y'),
                ),
                severity: $daysRemaining <= 2 ? 'danger' : 'warning',
                daysRemaining: $daysRemaining,
                expiresAt: $subscription->trial_ends_at,
                graceDaysAfterExpiry: 0,
            );
        }

        return new SubscriptionRenewalNotice(
            title: 'Período de teste terminando em breve',
            message: sprintf(
                'Seu teste termina %s (%s). Após o vencimento, você terá %d %s de tolerância para regularizar antes do bloqueio do painel.',
                $this->formatDaysRemaining($daysRemaining),
                $subscription->trial_ends_at->format('d/m/Y'),
                $this->graceDays(),
                $this->graceDays() === 1 ? 'dia' : 'dias',
            ),
            severity: $daysRemaining <= 2 ? 'danger' : 'warning',
            daysRemaining: $daysRemaining,
            expiresAt: $subscription->trial_ends_at,
            graceDaysAfterExpiry: $this->graceDays(),
        );
    }

    private function approachingCycleEndNotice(Subscription $subscription): ?SubscriptionRenewalNotice
    {
        if ($subscription->ends_at === null) {
            return null;
        }

        $daysRemaining = $this->daysUntil($subscription->ends_at);

        if ($daysRemaining > $this->noticeDays()) {
            return null;
        }

        return new SubscriptionRenewalNotice(
            title: 'Assinatura próxima do vencimento',
            message: sprintf(
                'Sua assinatura vence %s (%s). Após o vencimento, você terá %d %s de tolerância para regularizar antes do bloqueio do painel.',
                $this->formatDaysRemaining($daysRemaining),
                $subscription->ends_at->format('d/m/Y'),
                $this->graceDays(),
                $this->graceDays() === 1 ? 'dia' : 'dias',
            ),
            severity: $daysRemaining <= 2 ? 'danger' : 'warning',
            daysRemaining: $daysRemaining,
            expiresAt: $subscription->ends_at,
            graceDaysAfterExpiry: $this->graceDays(),
        );
    }

    private function daysUntil(Carbon $date): int
    {
        return max(0, (int) now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false));
    }

    private function formatDaysRemaining(int $days): string
    {
        return match ($days) {
            0 => 'hoje',
            1 => 'em 1 dia',
            default => "em {$days} dias",
        };
    }

    private function noticeDays(): int
    {
        return (int) config('subscriptions.renewal_notice_days', 7);
    }

    private function graceDays(): int
    {
        return (int) config('subscriptions.grace_days', 5);
    }
}
