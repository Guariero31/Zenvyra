<?php

namespace App\Filament\App\Resources\Orders\Pages;

use App\Filament\App\Resources\Orders\Actions\CancelOrderAction;
use App\Filament\App\Resources\Orders\Actions\RefundOrderAction;
use App\Filament\App\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RefundOrderAction::make(),
            CancelOrderAction::make(),
            DeleteAction::make(),
        ];
    }
}
