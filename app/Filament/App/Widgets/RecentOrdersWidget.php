<?php

declare(strict_types=1);

namespace App\Filament\App\Widgets;

use App\Filament\App\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Pedidos recentes';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->with(['customer'])
                    ->latest()
                    ->limit(5)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->formatStateUsing(fn (int $state): string => "#{$state}")
                    ->weight(FontWeight::SemiBold)
                    ->color('primary'),

                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->placeholder('Sem cliente'),

                TextColumn::make('total_amount')
                    ->label('Valor')
                    ->money('BRL')
                    ->weight(FontWeight::Medium),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m H\hi')
                    ->color('gray'),
            ])
            ->headerActions([
                Action::make('ver_todos')
                    ->label('Ver todos')
                    ->icon(Heroicon::OutlinedArrowRight)
                    ->url(OrderResource::getUrl('index'))
                    ->color('gray'),
            ])
            ->recordUrl(
                fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record])
            );
    }
}
