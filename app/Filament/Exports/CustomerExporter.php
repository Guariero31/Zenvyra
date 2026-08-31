<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Customer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CustomerExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),

            ExportColumn::make('name')
                ->label('Nome'),

            ExportColumn::make('phone')
                ->label('Telefone'),

            ExportColumn::make('email')
                ->label('E-mail'),

            ExportColumn::make('orders_count')
                ->label('Total de Pedidos')
                ->counts('orders'),

            ExportColumn::make('created_at')
                ->label('Cadastrado em')
                ->formatStateUsing(fn ($state): string => $state?->format('d/m/Y') ?? ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = number_format($export->successful_rows);

        return "Exportação de clientes concluída: {$count} ".($export->successful_rows === 1 ? 'registro' : 'registros').' exportados.';
    }

    public static function getCompletedNotificationTitle(Export $export): string
    {
        return 'Exportação de clientes concluída';
    }
}
