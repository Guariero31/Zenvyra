<?php

namespace App\Filament\Admin\Resources\Subscriptions\Pages;

use App\Filament\Admin\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Concerns\RedirectsToIndexAfterCreate;
use App\Services\SubscriptionManager;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

class CreateSubscription extends CreateRecord
{
    use RedirectsToIndexAfterCreate;

    protected static string $resource = SubscriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $start = ! empty($data['starts_at']) ? Carbon::parse($data['starts_at']) : now();
        $data['next_billing_at'] = app(SubscriptionManager::class)->nextBillingDate($start);

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Assinatura criada';
    }
}
