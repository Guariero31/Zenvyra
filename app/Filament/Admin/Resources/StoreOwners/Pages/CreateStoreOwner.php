<?php

namespace App\Filament\Admin\Resources\StoreOwners\Pages;

use App\Filament\Admin\Resources\StoreOwners\StoreOwnerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStoreOwner extends CreateRecord
{
    protected static string $resource = StoreOwnerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_platform_admin'] = false;

        return $data;
    }
}
