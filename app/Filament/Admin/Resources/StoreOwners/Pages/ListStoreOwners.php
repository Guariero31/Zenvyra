<?php

namespace App\Filament\Admin\Resources\StoreOwners\Pages;

use App\Filament\Admin\Resources\StoreOwners\StoreOwnerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStoreOwners extends ListRecords
{
    protected static string $resource = StoreOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
