<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Enums\TenantPortalRole;
use App\Models\Tenant;
use App\Support\TenantGestaoSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Toggle;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Auth\Authenticatable;

class SettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Configurações';

    protected static \UnitEnum|string|null $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 80;

    protected static ?string $slug = 'configuracoes/geral';

    protected static ?string $title = 'Configurações da Loja';

    protected string $view = 'filament-panels::pages.page';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        /** @var Authenticatable|FilamentUser|null $user */
        $user = auth()->user();
        $tenant = Filament::getTenant();

        if (! $user || ! $tenant instanceof Tenant) {
            return false;
        }

        $role = $user->roleInTenant($tenant);

        return in_array($role, [TenantPortalRole::AdministradorCliente, TenantPortalRole::Leader], true);
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $tenant = Filament::getTenant();

        abort_unless($tenant instanceof Tenant, 404);

        $this->form->fill($tenant->gestaoSettings()->toArray());
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedSchema::make('form'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Toggle::make('exige_telefone_cliente')
                ->label('Exigir telefone no cadastro de clientes')
                ->helperText('Quando ativo, não é possível salvar cliente sem telefone.')
                ->columnSpanFull(),

            Toggle::make('exige_email_cliente')
                ->label('Exigir e-mail no cadastro de clientes')
                ->helperText('Quando ativo, não é possível salvar cliente sem e-mail.')
                ->columnSpanFull(),

            Toggle::make('modulo_vendedores_ativo')
                ->label('Habilitar comissões por usuário')
                ->helperText('Desative para ocultar comissões nos pedidos e no ranking de vendedores.')
                ->columnSpanFull(),

            Toggle::make('permitir_estoque_negativo')
                ->label('Permitir estoque negativo')
                ->helperText('Quando desativado, vendas e ajustes não podem deixar o saldo abaixo de zero.')
                ->columnSpanFull(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar configurações')
                ->icon(Heroicon::OutlinedCheck)
                ->action(fn () => $this->save()),
        ];
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $tenant = Filament::getTenant();

        abort_unless($tenant instanceof Tenant, 404);

        $data = $this->form->getState();

        $tenant->update([
            'te_gestao_settings' => TenantGestaoSettings::from($data)->toArray(),
        ]);

        Notification::make()
            ->title('Configurações salvas')
            ->body('As alterações já estão valendo para toda a equipe.')
            ->success()
            ->send();
    }
}
