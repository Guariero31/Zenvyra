<?php

namespace App\Filament\Admin\Resources\Subscriptions\Pages;

use App\Filament\Admin\Resources\Subscriptions\SubscriptionResource;
use App\Services\SubscriptionManager;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $start = ! empty($data['starts_at']) ? Carbon::parse($data['starts_at']) : now();
        $data['next_billing_at'] = app(SubscriptionManager::class)->nextBillingDate($start);

        return $data;
    }
}
