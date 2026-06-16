<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum TenantPortalRole: string implements HasColor, HasIcon, HasLabel
{
    case Member = 'member';
    case Leader = 'leader';
    case Rh = 'rh';
    case AdministradorCliente = 'administrador_cliente';

    public function getLabel(): string
    {
        return match ($this) {
            self::Member => 'Colaborador',
            self::Leader => 'Líder',
            self::Rh => 'RH',
            self::AdministradorCliente => 'Administrador da Loja',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Member => 'gray',
            self::Leader => 'info',
            self::Rh => 'warning',
            self::AdministradorCliente => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Member => Heroicon::OutlinedUser,
            self::Leader => Heroicon::OutlinedUserGroup,
            self::Rh => Heroicon::OutlinedIdentification,
            self::AdministradorCliente => Heroicon::OutlinedShieldCheck,
        };
    }
}
