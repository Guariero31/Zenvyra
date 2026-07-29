<?php

namespace App\Filament\App\Resources\Customers\Pages;

use App\Filament\App\Resources\Customers\CustomerResource;
use App\Filament\Exports\CustomerExporter;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make('export')
                ->exporter(CustomerExporter::class)
                ->label('Exportar')
                ->color('gray')
                ->outlined(),

            CreateAction::make(),
        ];
    }
}
