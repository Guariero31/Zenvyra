<?php

namespace App\Filament\App\Resources\Products\RelationManagers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Leandrocfe\FilamentPtbrFormFields\Money;

class VariationsRelationManager extends RelationManager
{
    protected static string $relationship = 'variations';

    protected static ?string $title = 'Variações';

    protected static ?string $modelLabel = 'variação';

    protected static ?string $pluralModelLabel = 'variações';

    /** Aba só aparece quando o produto tem o toggle "Tem variações?" ativado. */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord instanceof Product && $ownerRecord->hasVariations();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('stock')
                    ->label('Estoque')
                    ->integer()
                    ->type('text')
                    ->extraInputAttributes(['x-on:focus' => '$el.select()'])
                    ->default(0)
                    ->required(),
                TextInput::make('low_stock_threshold')
                    ->label('Alerta de estoque baixo')
                    ->integer()
                    ->type('text')
                    ->extraInputAttributes(['x-on:focus' => '$el.select()'])
                    ->default(5)
                    ->required()
                    ->helperText('Destaca a variação quando o saldo atingir este valor.'),
                Money::make('price')
                    ->label('Preço (opcional)')
                    ->nullable()
                    ->helperText('Deixe vazio para usar o preço do produto.'),
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
                TextColumn::make('stock')
                    ->label('Estoque')
                    ->sortable(),
                TextColumn::make('low_stock_threshold')
                    ->label('Alerta de estoque baixo')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->placeholder('Preço do produto')
                    ->sortable(),
                TextColumn::make('images_count')
                    ->label('Imagens')
                    ->counts('images')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->emptyStateHeading('Sem variações')
            ->emptyStateDescription('Crie uma variação para começar.')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('manageImages')
                    ->label('Imagens')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->iconButton()
                    ->schema([
                        FileUpload::make('images')
                            ->label('Adicionar imagens')
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
                    ->action(function (ProductVariation $record, array $data): void {
                        foreach ($data['images'] as $path) {
                            ProductImage::create([
                                'product_id' => $record->product_id,
                                'product_variation_id' => $record->id,
                                'image_path' => $path,
                            ]);
                        }
                    })
                    ->successNotificationTitle('Imagens da variação adicionadas com sucesso'),
                EditAction::make()
                    ->label(false),
                DeleteAction::make()
                    ->label(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
