<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum SubscriptionIntent: string implements HasColor, HasIcon, HasLabel
{
    case Evaluation = 'evaluation';
    case Commercial = 'commercial';

    public function getLabel(): string
    {
        return match ($this) {
            self::Evaluation => 'Avaliação',
            self::Commercial => 'Comercial',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Evaluation => 'info',
            self::Commercial => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Evaluation => Heroicon::OutlinedBeaker,
            self::Commercial => Heroicon::OutlinedBuildingStorefront,
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $intent): array => [$intent->value => $intent->getLabel()])
            ->all();
    }
}
