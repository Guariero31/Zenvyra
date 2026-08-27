<?php

namespace App\Filament\Admin\Resources\Administrators\Pages;

use App\Filament\Admin\Resources\Administrators\AdministratorResource;
use App\Filament\Concerns\RedirectsToIndexAfterCreate;
use Filament\Resources\Pages\CreateRecord;

class CreateAdministrator extends CreateRecord
{
    use RedirectsToIndexAfterCreate;

    protected static string $resource = AdministratorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_platform_admin'] = true;

        return $data;
    }
}
