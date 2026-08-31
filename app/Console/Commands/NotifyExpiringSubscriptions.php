<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SubscriptionStatus;
use App\Enums\TenantPortalRole;
use App\Mail\SubscriptionExpiringMail;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Percorre as assinaturas que vencem dentro da janela de aviso configurada
 * (subscriptions.renewal_notice_days) e envia um e-mail para todos os
 * administradores de cada loja afetada.
 *
 * Agendado diariamente em routes/console.php.
 */
class NotifyExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:notify-expiring';

    protected $description = 'Envia aviso por e-mail para lojas com assinatura próxima do vencimento';

    public function handle(): int
    {
        $noticeDays = (int) config('subscriptions.renewal_notice_days', 7);
        $graceDays = (int) config('subscriptions.grace_days', 5);

        $liveStatuses = [
            SubscriptionStatus::Trialing->value,
            SubscriptionStatus::Active->value,
            SubscriptionStatus::PastDue->value,
        ];

        $notified = 0;
        $now = now();
        $threshold = $now->copy()->addDays($noticeDays);

        Subscription::query()
            ->with(['tenant', 'plan'])
            ->whereIn('status', $liveStatuses)
            ->where(function ($query) use ($now, $threshold): void {
                // Trial expirando
                $query->where(function ($q) use ($now, $threshold): void {
                    $q->whereNotNull('trial_ends_at')
                        ->whereBetween('trial_ends_at', [$now, $threshold]);
                })
                // Ciclo ativo expirando
                    ->orWhere(function ($q) use ($now, $threshold): void {
                        $q->whereNotNull('ends_at')
                            ->whereBetween('ends_at', [$now, $threshold]);
                    });
            })
            ->each(function (Subscription $subscription) use ($graceDays, &$notified): void {
                $tenant = $subscription->tenant;
                $plan = $subscription->plan;

                if (! $tenant instanceof Tenant || ! $plan) {
                    return;
                }

                $expiresAt = $subscription->trial_ends_at ?? $subscription->ends_at;

                if (! $expiresAt) {
                    return;
                }

                $daysRemaining = (int) now()->diffInDays($expiresAt, false);

                $admins = $tenant->users()
                    ->wherePivot('tu_papel', TenantPortalRole::AdministradorCliente->value)
                    ->get();

                $admins->each(function (User $admin) use ($tenant, $plan, $daysRemaining, $expiresAt, $graceDays) {
                    Mail::to($admin->email)->send(new SubscriptionExpiringMail(
                        storeName: $tenant->displayName(),
                        planName: $plan->name,
                        daysRemaining: max(0, $daysRemaining),
                        expiresAt: $expiresAt->format('d/m/Y'),
                        graceDays: $graceDays,
                    ));
                });

                $notified++;
            });

        $this->info("Avisos de vencimento enviados para {$notified} assinatura(s).");

        return self::SUCCESS;
    }
}
