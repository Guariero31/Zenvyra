<?php

namespace App\Filament\Admin\Resources\Tenants\RelationManagers;

use App\Enums\TenantPortalRole;
use App\Models\Tenant;
use App\Services\PlanLimitService;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Usuários';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('pivot.tu_papel')
                    ->label('Papel')
                    ->badge(),
                TextColumn::make('pivot.tu_rotulo_setor')
                    ->label('Setor')
                    ->placeholder('-'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Vincular usuário')
                    ->before(function (AttachAction $action): void {
                        $tenant = $action->getLivewire()->getOwnerRecord();

                        if (!$tenant instanceof Tenant) {
                            return;
                        }

                        try {
                            app(PlanLimitService::class)->assertCanAddUser($tenant);
                        } catch (\RuntimeException $exception) {
                            Notification::make()
                                ->title($exception->getMessage())
                                ->danger()
                                ->send();

                            $action->halt();
                        }
                    })
                    ->schema(fn(AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->label('Usuário')
                            ->searchable()
                            ->preload(),
                        Select::make('tu_papel')
                            ->label('Papel')
                            ->options(TenantPortalRole::class)
                            ->required(),
                        Select::make('tu_rotulo_setor')
                            ->label('Setor')
                            ->options([
                                'Diretoria' => 'Diretoria',
                                'Gerência' => 'Gerência',
                                'Vendas' => 'Vendas',
                                'Operação' => 'Operação',
                                'RH' => 'RH',
                            ])
                            ->searchable(),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
