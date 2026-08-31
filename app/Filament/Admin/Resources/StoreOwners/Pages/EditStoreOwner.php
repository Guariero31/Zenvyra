<?php

namespace App\Filament\Admin\Resources\StoreOwners\Pages;

use App\Filament\Admin\Actions\ToggleAdminStatusAction;
use App\Filament\Admin\Resources\StoreOwners\StoreOwnerResource;
use Filament\Resources\Pages\EditRecord;

class EditStoreOwner extends EditRecord
{
    protected static string $resource = StoreOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleAdminStatusAction::make(),
        ];
    }
}
