<?php

namespace App\Filament\Admin\Resources\Tenants\Pages;

use App\Enums\SubscriptionIntent;
use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Filament\Concerns\RedirectsToIndexAfterCreate;
use App\Models\Plan;
use App\Services\SubscriptionManager;
use App\Support\TenantGestaoSettings;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    use RedirectsToIndexAfterCreate;

    protected static string $resource = TenantResource::class;

    private SubscriptionIntent $provisioningType = SubscriptionIntent::Evaluation;

    private ?int $evaluationDays = null;

    private ?int $commercialPlanId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->provisioningType = SubscriptionIntent::from($data['provisioning_type'] ?? SubscriptionIntent::Evaluation->value);
        $this->evaluationDays = isset($data['evaluation_days']) ? (int) $data['evaluation_days'] : null;
        $this->commercialPlanId = isset($data['commercial_plan_id']) ? (int) $data['commercial_plan_id'] : null;

        unset($data['provisioning_type'], $data['evaluation_days'], $data['commercial_plan_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->subscriptions()->exists()) {
            return;
        }

        $manager = app(SubscriptionManager::class);

        if ($this->provisioningType === SubscriptionIntent::Commercial) {
            $plan = Plan::find($this->commercialPlanId);

            if ($plan) {
                $manager->startCommercialSubscription($this->record, $plan);
            }

            return;
        }

        $plan = Plan::firstWhere('slug', config('subscriptions.evaluation_plan_slug'));

        if ($plan) {
            $manager->startEvaluation($this->record, $plan, $this->evaluationDays);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['te_gestao_settings'] = TenantGestaoSettings::from($data['te_gestao_settings'] ?? null)->toArray();

        return $data;
    }
}
