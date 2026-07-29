<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),

            ExportColumn::make('title')
                ->label('Produto'),

            ExportColumn::make('sku')
                ->label('SKU'),

            ExportColumn::make('category.name')
                ->label('Categoria'),

            ExportColumn::make('price')
                ->label('Preço de Venda (R$)'),

            ExportColumn::make('cost_price')
                ->label('Custo (R$)'),

            ExportColumn::make('commission_rate')
                ->label('Comissão (%)')
                ->formatStateUsing(fn ($state): string => $state !== null ? (string) $state : 'Padrão'),

            ExportColumn::make('stock')
                ->label('Estoque')
                ->formatStateUsing(fn (Product $record): int => $record->effectiveStock()),

            ExportColumn::make('low_stock_threshold')
                ->label('Alerta de Estoque Baixo')
                ->formatStateUsing(fn ($state): string => $state > 0 ? (string) $state : 'Desativado'),

            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'active'   => 'Ativo',
                    'inactive' => 'Inativo',
                    'draft'    => 'Rascunho',
                    default    => $state,
                }),

            ExportColumn::make('created_at')
                ->label('Cadastrado em')
                ->formatStateUsing(fn ($state): string => $state?->format('d/m/Y') ?? ''),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $count = number_format($export->successful_rows);

        return "Exportação de produtos concluída: {$count} " . ($export->successful_rows === 1 ? 'registro' : 'registros') . ' exportados.';
    }

    public static function getCompletedNotificationTitle(Export $export): string
    {
        return 'Exportação de produtos concluída';
    }
}
