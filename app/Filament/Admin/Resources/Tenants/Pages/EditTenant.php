<?php

namespace App\Filament\Admin\Resources\Tenants\Pages;

use App\Filament\Admin\Resources\Tenants\Actions\ToggleTenantStatusAction;
use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Support\TenantGestaoSettings;
use Filament\Resources\Pages\EditRecord;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['te_gestao_settings'] = $this->record->gestaoSettings()->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['te_gestao_settings'] = TenantGestaoSettings::from($data['te_gestao_settings'] ?? null)->toArray();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ToggleTenantStatusAction::make(),
        ];
    }
}
