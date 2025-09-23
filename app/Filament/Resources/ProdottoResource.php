<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\ProdottoResource\Pages\ListProdottos;
use App\Filament\Resources\ProdottoResource\Pages\CreateProdotto;
use App\Filament\Resources\ProdottoResource\Pages\EditProdotto;
use App\Filament\Resources\ProdottoResource\Pages;
use App\Filament\Resources\ProdottoResource\RelationManagers;
use App\Models\Prodotto;
use Filament\Actions\ReplicateAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdottoResource extends Resource
{
    protected static ?string $model = Prodotto::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-cart';


    public static function getPluralLabel(): ?string
    {
        return "Prodotti";
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('nome_breve')
                    ->required()
                    ->maxLength(255),
                TextInput::make('prezzo')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Select::make('categoria_id')
                    ->relationship('categoria', 'nome')
                    ->required(),
                TextInput::make('ordine')
                    ->required()
                    ->numeric(),
                Toggle::make('coperto'),
                Toggle::make('attivo')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->searchable(),
                TextColumn::make('nome_breve')
                    ->searchable(),
                TextColumn::make('prezzo')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('categoria.id')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('ordine')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('attivo')
                    ->boolean(),
                TextColumn::make('evento.data')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ReplicateAction::make(),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProdottos::route('/'),
            'create' => CreateProdotto::route('/create'),
            'edit' => EditProdotto::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
