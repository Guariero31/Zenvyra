<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\ActivityLogs\Pages;

use App\Filament\App\Resources\ActivityLogs\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
