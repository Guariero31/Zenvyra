<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = 1;
}
