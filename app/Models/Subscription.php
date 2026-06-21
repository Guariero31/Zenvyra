<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionIntent;
use App\Enums\SubscriptionStatus;
use App\Services\SubscriptionManager;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'intent',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'grace_ends_at',
        'next_billing_at',
        'canceled_at',
        'gateway',
        'gateway_subscription_id',
        'gateway_customer_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'intent' => SubscriptionIntent::class,
            'trial_ends_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'grace_ends_at' => 'datetime',
            'next_billing_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function onTrial(): bool
    {
        return $this->status === SubscriptionStatus::Trialing
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function onGracePeriod(): bool
    {
        return $this->status === SubscriptionStatus::PastDue
            && $this->grace_ends_at !== null
            && $this->grace_ends_at->isFuture();
    }

    /**
     * Decide se a assinatura libera o acesso ao painel da loja. Combina o status
     * com as datas: trial vigente, ativa dentro do período ou atrasada ainda
     * dentro da tolerância.
     */
    public function grantsAccess(): bool
    {
        return $this->onTrial() || $this->isActive() || $this->onGracePeriod();
    }

    public function isEvaluation(): bool
    {
        return $this->intent === SubscriptionIntent::Evaluation;
    }

    /**
     * Data exibida como próxima cobrança. Usa next_billing_at quando persistido;
     * caso contrário, infere a partir do ciclo ou do fim do teste/avaliação.
     */
    public function resolveNextBillingAt(): ?Carbon
    {
        if ($this->next_billing_at !== null) {
            return $this->next_billing_at;
        }

        $resolved = match ($this->status) {
            SubscriptionStatus::Trialing => $this->trial_ends_at,
            SubscriptionStatus::Active => $this->ends_at,
            SubscriptionStatus::PastDue => $this->grace_ends_at ?? $this->ends_at,
            default => null,
        };

        if ($resolved !== null) {
            return $resolved;
        }

        if ($this->starts_at === null) {
            return null;
        }

        return match ($this->status) {
            SubscriptionStatus::Trialing,
            SubscriptionStatus::Active,
            SubscriptionStatus::PastDue => app(SubscriptionManager::class)->nextBillingDate($this->starts_at),
            default => null,
        };
    }

    /**
     * @param  Builder<Subscription>  $query
     * @return Builder<Subscription>
     */
    public function scopeExpiringEvaluations(Builder $query, ?int $withinDays = null): Builder
    {
        $days = $withinDays ?? (int) config('subscriptions.evaluation_expiring_alert_days', 7);

        return $query
            ->where('intent', SubscriptionIntent::Evaluation)
            ->where('status', SubscriptionStatus::Trialing)
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays($days)])
            ->orderBy('trial_ends_at');
    }

    public function daysUntilTrialEnds(): ?int
    {
        if ($this->trial_ends_at === null) {
            return null;
        }

        return (int) now()->diffInDays($this->trial_ends_at, false);
    }
}
