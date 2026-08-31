<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum StockMovementType: string implements HasColor, HasIcon, HasLabel
{
    case Entry = 'entry';
    case Sale = 'sale';
    case Refund = 'refund';
    case Adjustment = 'adjustment';

    public function getLabel(): string
    {
        return match ($this) {
            self::Entry => 'Entrada',
            self::Sale => 'Venda',
            self::Refund => 'Estorno',
            self::Adjustment => 'Ajuste',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Entry => 'success',
            self::Sale => 'danger',
            self::Refund => 'info',
            self::Adjustment => 'warning',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Entry => Heroicon::OutlinedArrowDownTray,
            self::Sale => Heroicon::OutlinedArrowUpTray,
            self::Refund => Heroicon::OutlinedArrowUturnLeft,
            self::Adjustment => Heroicon::OutlinedAdjustmentsHorizontal,
        };
    }

    /**
     * Direção que o movimento aplica sobre o saldo de estoque.
     */
    public function isInbound(): bool
    {
        return match ($this) {
            self::Sale => false,
            default => true,
        };
    }
}
