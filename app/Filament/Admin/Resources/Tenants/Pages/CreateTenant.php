<?php

namespace App\Filament\Admin\Resources\Tenants\Pages;

use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Models\Plan;
use App\Services\SubscriptionManager;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    /**
     * Inicia automaticamente a assinatura da loja recém-criada com o plano
     * padrão, evitando que ela nasça bloqueada pelo middleware de assinatura.
     * Se o plano padrão não existir, a loja segue sem assinatura.
     */
    protected function afterCreate(): void
    {
        if ($this->record->subscriptions()->exists()) {
            return;
        }

        $plan = Plan::firstWhere('slug', config('subscriptions.default_plan_slug'));

        if ($plan) {
            app(SubscriptionManager::class)->startSubscription($this->record, $plan);
        }
    }
}
