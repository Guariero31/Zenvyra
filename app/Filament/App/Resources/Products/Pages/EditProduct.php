<?php

namespace App\Filament\App\Resources\Products\Pages;

use App\Filament\App\Resources\Products\Actions\StockEntryAction;
use App\Filament\App\Resources\Products\Actions\ToggleProductStatusAction;
use App\Filament\App\Resources\Products\ProductResource;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            StockEntryAction::make(),
            ToggleProductStatusAction::make(),
        ];
    }
}
