<?php

declare(strict_types=1);

namespace App\Filament\App\Pages;

use App\Enums\TenantPortalRole;
use App\Models\Tenant;
use App\Models\TenantTheme;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Auth\Authenticatable;

class StoreConfigPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $navigationLabel = 'Site Público';

    protected static \UnitEnum|string|null $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 81;

    protected static ?string $slug = 'configuracoes/site';

    protected static ?string $title = 'Configurações do Site Público';

    protected string $view = 'filament-panels::pages.page';

    /** @var array<string, mixed> */
    public array $data = [];

    public static function canAccess(): bool
    {
        /** @var Authenticatable|FilamentUser|null $user */
        $user  = auth()->user();
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

        $theme = TenantTheme::obterOuCriarParaTenant((int) $tenant->getKey());

        $config = $theme->store_config ?? [];

        $this->form->fill([
            'whatsapp'        => $config['whatsapp']    ?? '',
            'email'           => $config['email']       ?? '',
            'endereco'        => $config['endereco']    ?? '',
            'ticker_items'    => $config['ticker_items'] ?? [],
            'carousel_slides' => $config['carousel_slides'] ?? [],
        ]);
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
        $tenant   = Filament::getTenant();
        $tenantId = $tenant instanceof Tenant ? (int) $tenant->getKey() : 0;

        return $schema->schema([
            Tabs::make('StoreConfigTabs')
                ->columnSpanFull()
                ->tabs([

                    Tab::make('Contato')
                        ->icon(Heroicon::OutlinedPhone)
                        ->schema([
                            Section::make('Informações de contato')
                                ->description('Exibidas na página Contato do seu site público.')
                                ->schema([
                                    TextInput::make('whatsapp')
                                        ->label('WhatsApp')
                                        ->placeholder('+55 11 9 9999-9999')
                                        ->helperText('Inclua o código do país. Ex: +55 11 9 8765-4321')
                                        ->tel()
                                        ->maxLength(30),

                                    TextInput::make('email')
                                        ->label('E-mail de contato')
                                        ->placeholder('contato@sualoja.com.br')
                                        ->email()
                                        ->maxLength(100),

                                    Textarea::make('endereco')
                                        ->label('Endereço')
                                        ->placeholder('Rua das Flores, 123 — Centro, São Paulo – SP')
                                        ->rows(2)
                                        ->maxLength(200)
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Tab::make('Carousel')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->schema([
                            Section::make('Slides do carousel')
                                ->description('Configure até 4 slides que aparecem na home. Se vazio, o site usará os produtos em destaque automaticamente.')
                                ->schema([
                                    Repeater::make('carousel_slides')
                                        ->label('')
                                        ->schema([
                                            FileUpload::make('image_path')
                                                ->label('Imagem de fundo')
                                                ->helperText('JPG ou PNG. Recomendado: 1920×1080 px.')
                                                ->image()
                                                ->imagePreviewHeight('120px')
                                                ->disk('public')
                                                ->directory('store-banners/tenant-'.$tenantId)
                                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                                ->maxSize(4096)
                                                ->columnSpanFull(),

                                            TextInput::make('eyebrow')
                                                ->label('Olho (texto pequeno acima)')
                                                ->placeholder('Ex: Lançamento · Coleção Verão')
                                                ->maxLength(60),

                                            TextInput::make('title')
                                                ->label('Título do slide')
                                                ->placeholder('Ex: Descubra o novo verão')
                                                ->maxLength(80)
                                                ->required(),

                                            TextInput::make('subtitle')
                                                ->label('Subtítulo')
                                                ->placeholder('Ex: Peças exclusivas com entrega rápida')
                                                ->maxLength(120),
                                        ])
                                        ->columns(2)
                                        ->maxItems(4)
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                                        ->addActionLabel('Adicionar slide')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Tab::make('Ticker')
                        ->icon(Heroicon::OutlinedMegaphone)
                        ->schema([
                            Section::make('Faixa animada')
                                ->description('Textos que rolam em loop abaixo do carousel. Se vazio, exibe as categorias de produtos automaticamente.')
                                ->schema([
                                    Repeater::make('ticker_items')
                                        ->label('')
                                        ->schema([
                                            TextInput::make('text')
                                                ->label('Texto')
                                                ->placeholder('Ex: Frete grátis acima de R$ 150')
                                                ->maxLength(80)
                                                ->required(),
                                        ])
                                        ->reorderable()
                                        ->collapsible(false)
                                        ->itemLabel(fn (array $state): ?string => $state['text'] ?? null)
                                        ->addActionLabel('Adicionar item')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visit_site')
                ->label('Ver site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => route('store.home', Filament::getTenant()?->te_slug ?? ''))
                ->openUrlInNewTab(),

            Action::make('save')
                ->label('Salvar')
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

        $theme = TenantTheme::obterOuCriarParaTenant((int) $tenant->getKey());

        $theme->update([
            'store_config' => [
                'whatsapp'        => $data['whatsapp']    ?: null,
                'email'           => $data['email']       ?: null,
                'endereco'        => $data['endereco']    ?: null,
                'ticker_items'    => $data['ticker_items']    ?? [],
                'carousel_slides' => $data['carousel_slides'] ?? [],
            ],
        ]);

        Notification::make()
            ->title('Site salvo')
            ->body('As alterações já estão no ar no seu site público.')
            ->success()
            ->send();
    }
}
