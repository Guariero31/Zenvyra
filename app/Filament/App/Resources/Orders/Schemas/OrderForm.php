<?php

namespace App\Filament\App\Resources\Orders\Schemas;

use App\Enums\OrderOrigin;
use App\Enums\OrderStatus;
use App\Filament\App\Resources\Orders\OrderResource;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Leandrocfe\FilamentPtbrFormFields\Money;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id')
                            ->label('Cliente')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('origin')
                            ->label('Origem')
                            ->options(OrderOrigin::class)
                            ->default(OrderOrigin::Balcao->value)
                            ->required(),
                        Select::make('user_id')
                            ->label('Vendedor')
                            ->options(fn (): array => Filament::getTenant()?->users()
                                ->orderBy('name')
                                ->pluck('name', 'users.id')
                                ->all() ?? [])
                            ->default(fn (): ?int => auth()->id())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (): bool => ! OrderResource::userManagesOrders())
                            ->helperText(fn (): ?string => OrderResource::userManagesOrders()
                                ? 'Usuário logado responsável pela venda e comissão.'
                                : 'A venda será registrada em seu usuário.'),
                        Money::make('total_amount')
                            ->label('Total')
                            ->disabled()
                            ->dehydrated(false),
                        Money::make('commission_amount')
                            ->label('Comissão')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('payment_method')
                            ->label('Pagamento')
                            ->options([
                                'dinheiro' => 'Dinheiro',
                                'pix' => 'Pix',
                                'cartao_credito' => 'Cartão de crédito',
                                'cartao_debito' => 'Cartão de débito',
                            ])
                            ->searchable(),
                        Select::make('status')
                            ->label('Status')
                            ->options(OrderStatus::editableOptions())
                            ->default(OrderStatus::Pending->value)
                            ->disabled(fn (OrderStatus|string|null $state): bool => match (true) {
                                $state instanceof OrderStatus => ! $state->isManuallyEditable(),
                                is_string($state) => ! OrderStatus::from($state)->isManuallyEditable(),
                                default => false,
                            })
                            ->required(),
                        DatePicker::make('created_at')
                            ->label('Criado em')
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
