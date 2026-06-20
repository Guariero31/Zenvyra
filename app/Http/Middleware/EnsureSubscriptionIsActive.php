<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\SubscriptionManager;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garante que a loja resolvida no painel só seja acessível enquanto a assinatura
 * estiver vigente (trial, ativa ou em tolerância). Caso contrário, redireciona
 * para a tela de assinatura pendente, fechando o acesso de lojas inadimplentes.
 */
class EnsureSubscriptionIsActive
{
    public function __construct(private readonly SubscriptionManager $subscriptionManager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if ($tenant instanceof Tenant) {
            $latestSubscription = $tenant->subscriptions()->latest('id')->first();

            if ($latestSubscription !== null) {
                $this->subscriptionManager->refreshState($latestSubscription);
                $tenant->unsetRelation('subscriptions');
            }

            if (! $tenant->hasActiveSubscription()) {
                return redirect()->route('subscription.blocked', ['tenant' => $tenant->te_slug]);
            }
        }

        return $next($request);
    }
}
