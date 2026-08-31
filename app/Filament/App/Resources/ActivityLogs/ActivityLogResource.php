<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\ActivityLogs;

use App\Filament\App\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\App\Resources\ActivityLogs\Tables\ActivityLogsTable;
use App\Models\ActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $modelLabel = 'log';

    protected static ?string $pluralModelLabel = 'logs de atividade';

    protected static ?string $navigationLabel = 'Auditoria';

    protected static \UnitEnum|string|null $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 90;

    public static function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
