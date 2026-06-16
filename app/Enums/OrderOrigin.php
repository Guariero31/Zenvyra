<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum OrderOrigin: string implements HasColor, HasIcon, HasLabel
{
    case Balcao = 'BALCAO';
    case CatalogoWhatsapp = 'CATALOGO_WHATSAPP';

    public function getLabel(): string
    {
        return match ($this) {
            self::Balcao => 'Balcão',
            self::CatalogoWhatsapp => 'Catálogo WhatsApp',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Balcao => 'gray',
            self::CatalogoWhatsapp => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Balcao => Heroicon::OutlinedBuildingStorefront,
            self::CatalogoWhatsapp => Heroicon::OutlinedChatBubbleLeftRight,
        };
    }
}
