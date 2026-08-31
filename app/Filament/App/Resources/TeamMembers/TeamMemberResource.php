<?php

namespace App\Filament\App\Resources\TeamMembers;

use App\Filament\App\Resources\TeamMembers\Pages\CreateTeamMember;
use App\Filament\App\Resources\TeamMembers\Pages\EditTeamMember;
use App\Filament\App\Resources\TeamMembers\Pages\ListTeamMembers;
use App\Filament\App\Resources\TeamMembers\Schemas\TeamMemberForm;
use App\Filament\App\Resources\TeamMembers\Tables\TeamMembersTable;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\TeamMemberPolicy;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TeamMemberResource extends Resource
{
    protected static ?string $model = User::class;

    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $modelLabel = 'membro da equipe';

    protected static ?string $pluralModelLabel = 'equipe';

    protected static ?string $navigationLabel = 'Equipe';

    protected static \UnitEnum|string|null $navigationGroup = 'Pessoas';

    protected static ?int $navigationSort = 31;

    protected static ?string $slug = 'configuracoes/equipe';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TeamMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeamMembersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $tenant = Filament::getTenant();

        if (! $tenant instanceof Tenant) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->whereHas('tenants', fn (Builder $query): Builder => $query->where('te_tenants.id', $tenant->getKey()))
            ->orderBy('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeamMembers::route('/'),
            'create' => CreateTeamMember::route('/create'),
            'edit' => EditTeamMember::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && app(TeamMemberPolicy::class)->create($user);
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $record instanceof User
            && app(TeamMemberPolicy::class)->delete($user, $record);
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && app(TeamMemberPolicy::class)->viewAny($user);
    }

    public static function canView(Model $record): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $record instanceof User
            && app(TeamMemberPolicy::class)->view($user, $record);
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $record instanceof User
            && app(TeamMemberPolicy::class)->update($user, $record);
    }
}
