<?php

namespace App\Filament\Admin\Resources\Plans\Pages;

use App\Filament\Admin\Resources\Plans\Actions\TogglePlanStatusAction;
use App\Filament\Admin\Resources\Plans\PlanResource;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            TogglePlanStatusAction::make(),
        ];
    }
}
