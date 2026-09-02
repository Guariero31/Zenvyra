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

    /**
     * 3 colunas: os widgets de gráfico/tabela usam columnSpan 2 (2/3) e 1
     * (1/3) para ficarem lado a lado nas linhas de baixo, seguindo o
     * layout de referência (Receita Mensal + Formas de Pagamento, e
     * Pedidos Recentes + Top Vendedores).
     */
    public function getColumns(): int|array
    {
        return 3;
    }

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
