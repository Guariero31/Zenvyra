<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = 1;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_pdv')
                ->label('Abrir PDV')
                ->icon(Heroicon::OutlinedShoppingCart)
                ->color('success')
                ->visible(fn (): bool => PdvPage::canAccess())
                ->url(fn (): string => PdvPage::getUrl()),
        ];
    }
}
