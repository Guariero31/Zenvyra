<?php

namespace App\Filament\Admin\Resources\Administrators\Pages;

use App\Filament\Admin\Actions\ToggleAdminStatusAction;
use App\Filament\Admin\Resources\Administrators\AdministratorResource;
use Filament\Resources\Pages\EditRecord;

class EditAdministrator extends EditRecord
{
    protected static string $resource = AdministratorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleAdminStatusAction::make(),
        ];
    }
}
