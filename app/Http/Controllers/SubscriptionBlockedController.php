<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class SubscriptionBlockedController extends Controller
{
    public function __invoke(Tenant $tenant): View
    {
        return view('subscriptions.blocked', [
            'tenant' => $tenant,
            'subscription' => $tenant->subscriptions()->latest('id')->first(),
            'otherTenants' => $this->accessibleTenants($tenant),
        ]);
    }

    /**
     * Demais lojas do usuário logado que ainda liberam acesso, para oferecer a
     * troca de contexto direto na tela de bloqueio.
     *
     * @return Collection<int, Tenant>
     */
    private function accessibleTenants(Tenant $current): Collection
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return collect();
        }

        return $user->tenants()
            ->where('te_tenants.id', '!=', $current->getKey())
            ->get()
            ->filter(fn (Tenant $tenant): bool => $tenant->hasActiveSubscription())
            ->values();
    }
}
