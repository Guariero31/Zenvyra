<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum SubscriptionStatus: string implements HasColor, HasIcon, HasLabel
{
    case Trialing = 'trialing';
    case Active = 'active';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::Trialing => 'Em teste',
            self::Active => 'Ativa',
            self::PastDue => 'Atrasada',
            self::Canceled => 'Cancelada',
            self::Expired => 'Expirada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Trialing => 'info',
            self::Active => 'success',
            self::PastDue => 'warning',
            self::Canceled => 'gray',
            self::Expired => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Trialing => Heroicon::OutlinedSparkles,
            self::Active => Heroicon::OutlinedCheckCircle,
            self::PastDue => Heroicon::OutlinedExclamationTriangle,
            self::Canceled => Heroicon::OutlinedXCircle,
            self::Expired => Heroicon::OutlinedNoSymbol,
        };
    }

    /**
     * Status "vivos": ainda dentro do ciclo de cobrança. A liberação real de
     * acesso depende também das datas e é decidida no model Subscription.
     */
    public function isLive(): bool
    {
        return match ($this) {
            self::Trialing, self::Active, self::PastDue => true,
            self::Canceled, self::Expired => false,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->getLabel()])
            ->all();
    }
}
