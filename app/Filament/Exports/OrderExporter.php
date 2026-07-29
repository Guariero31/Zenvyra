<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('# Pedido'),

            ExportColumn::make('created_at')
                ->label('Data')
                ->formatStateUsing(fn ($state): string => $state?->format('d/m/Y H:i') ?? ''),

            ExportColumn::make('customer.name')
                ->label('Cliente'),

            ExportColumn::make('operator.name')
                ->label('Vendedor'),

            ExportColumn::make('origin')
                ->label('Origem'),

            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn ($state): string => $state?->label() ?? (string) $state),

            ExportColumn::make('payment_method')
                ->label('Pagamento')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'dinheiro'       => 'Dinheiro',
                    'pix'            => 'Pix',
                    'cartao_credito' => 'Cartão de Crédito',
                    'cartao_debito'  => 'Cartão de Débito',
                    default          => $state ?? '',
                }),

            ExportColumn::make('total_amount')
                ->label('Total (R$)'),

            ExportColumn::make('refunded_amount')
                ->label('Estornado (R$)'),

            ExportColumn::make('commission_amount')
                ->label('Comissão (R$)'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = number_format($export->successful_rows);

        return "Exportação de pedidos concluída: {$count} " . ($export->successful_rows === 1 ? 'registro' : 'registros') . ' exportados.';
    }

    public static function getCompletedNotificationTitle(Export $export): string
    {
        return 'Exportação de pedidos concluída';
    }
}
