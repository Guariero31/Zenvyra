<?php

namespace App\Filament\App\Resources\Products\RelationManagers;

use App\Models\ProductImage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Imagens';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('image_path')
                    ->label('Imagem')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('image_path')
            ->columns([
                Stack::make([
                    ImageColumn::make('image_path')
                        ->label('Imagem')
                        ->disk('public')
                        ->height(200)
                        ->imageWidth('100%')
                        ->extraAttributes(['class' => 'w-full'])
                        ->extraImgAttributes(['class' => 'w-full object-cover rounded-lg'])
                        ->defaultImageUrl(asset('images/placeholder-product.svg')),
                    IconColumn::make('is_primary')
                        ->label('Capa')
                        ->icon(fn (bool $state): string => $state ? 'heroicon-s-star' : 'heroicon-o-star')
                        ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                        ->tooltip(fn (bool $state): string => $state ? 'Imagem de capa' : 'Imagem secundária'),
                    TextColumn::make('created_at')
                        ->label('Adicionada em')
                        ->since()
                        ->color('gray')
                        ->size(TextSize::Small),
                ])->space(2),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->paginated([12, 24, 48])
            ->emptyStateHeading('Nenhuma imagem ainda')
            ->emptyStateDescription('Adicione fotos do produto para exibir na galeria.')
            ->emptyStateIcon(Heroicon::OutlinedPhoto)
            ->headerActions([
                Action::make('uploadImages')
                    ->label('Adicionar imagens')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->schema([
                        FileUpload::make('images')
                            ->label('Imagens')
                            ->image()
                            ->imageEditor()
                            ->multiple()
                            ->reorderable()
                            ->panelLayout('grid')
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        foreach ($data['images'] as $path) {
                            $this->getOwnerRecord()->images()->create([
                                'image_path' => $path,
                            ]);
                        }
                    }),
            ])
            ->recordActions([
                Action::make('setPrimary')
                    ->label('Definir como capa')
                    ->icon(Heroicon::OutlinedStar)
                    ->color('warning')
                    ->iconButton()
                    ->visible(fn (ProductImage $record): bool => ! $record->is_primary)
                    ->action(function (ProductImage $record): void {
                        $record->update(['is_primary' => true]);

                        Notification::make()
                            ->title('Imagem definida como capa')
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label(false)
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton(),
            ]);
    }
}
