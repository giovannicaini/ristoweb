<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdottoResource\Pages;
use App\Filament\Resources\ProdottoResource\RelationManagers;
use App\Models\Prodotto;
use Filament\Actions\ReplicateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ReplicateAction as ActionsReplicateAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdottoResource extends Resource
{
    protected static ?string $model = Prodotto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function getPluralLabel(): ?string
    {
        return "Prodotti";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nome_breve')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('prezzo')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\Select::make('categoria_id')
                    ->relationship('categoria', 'nome')
                    ->required(),
                Forms\Components\TextInput::make('ordine')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('coperto'),
                Forms\Components\Toggle::make('attivo')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nome_breve')
                    ->searchable(),
                Tables\Columns\TextColumn::make('prezzo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoria.id')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ordine')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('attivo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('evento.data')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ActionsReplicateAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListProdottos::route('/'),
            'create' => Pages\CreateProdotto::route('/create'),
            'edit' => Pages\EditProdotto::route('/{record}/edit'),
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
