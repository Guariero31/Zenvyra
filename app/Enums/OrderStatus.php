<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Pending = 'pending';
    case Paid = 'paid';
    case PartiallyRefunded = 'partially_refunded';
    case Refunded = 'refunded';
    case Canceled = 'canceled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pendente',
            self::Paid => 'Pago',
            self::PartiallyRefunded => 'Parcialmente estornado',
            self::Refunded => 'Estornado',
            self::Canceled => 'Cancelado',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::PartiallyRefunded => 'info',
            self::Refunded => 'danger',
            self::Canceled => 'gray',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Pending => Heroicon::OutlinedClock,
            self::Paid => Heroicon::OutlinedCheckCircle,
            self::PartiallyRefunded => Heroicon::OutlinedArrowUturnLeft,
            self::Refunded => Heroicon::OutlinedReceiptRefund,
            self::Canceled => Heroicon::OutlinedXCircle,
        };
    }

    public function isRefundable(): bool
    {
        return match ($this) {
            self::Paid, self::PartiallyRefunded => true,
            default => false,
        };
    }

    public function isCancellable(): bool
    {
        return $this === self::Pending;
    }

    public function locksItems(): bool
    {
        return match ($this) {
            self::Paid, self::PartiallyRefunded, self::Refunded, self::Canceled => true,
            default => false,
        };
    }

    /**
     * Status manipulados apenas por ações dedicadas (estorno/cancelamento),
     * mantidos fora do Select para não dessincronizar estoque e financeiro.
     *
     * @return array<string, string>
     */
    public static function editableOptions(): array
    {
        return collect(self::cases())
            ->reject(fn (self $status): bool => in_array($status, [
                self::PartiallyRefunded,
                self::Refunded,
                self::Canceled,
            ], true))
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->getLabel()])
            ->all();
    }

    public function isManuallyEditable(): bool
    {
        return match ($this) {
            self::PartiallyRefunded, self::Refunded, self::Canceled => false,
            default => true,
        };
    }

    /**
     * Status que representam uma venda efetivada (já paga ao menos uma vez).
     * Base para faturamento, contagem de vendas e indicadores de BI.
     *
     * @return array<int, self>
     */
    public static function soldCases(): array
    {
        return [self::Paid, self::PartiallyRefunded, self::Refunded];
    }

    /**
     * @return array<int, string>
     */
    public static function soldValues(): array
    {
        return array_map(fn (self $status): string => $status->value, self::soldCases());
    }
}
