<?php

namespace App\Filament\Admin\Resources\Plans\Pages;

use App\Filament\Admin\Resources\Plans\PlanResource;
use App\Filament\Concerns\RedirectsToIndexAfterCreate;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    use RedirectsToIndexAfterCreate;

    protected static string $resource = PlanResource::class;
}
