<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComandaDettaglioResource\Pages;
use App\Filament\Resources\ComandaDettaglioResource\RelationManagers;
use App\Models\ComandaDettaglio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComandaDettaglioResource extends Resource
{
    protected static ?string $model = ComandaDettaglio::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getPluralLabel(): ?string
    {
        return "Comande Dettagli";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('comanda_id')
                    ->relationship('comanda', 'id')
                    ->required(),
                Forms\Components\Select::make('prodotto_id')
                    ->relationship('prodotto', 'id')
                    ->required(),
                Forms\Components\TextInput::make('quantita')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('prezzo_unitario')
                    ->numeric(),
                Forms\Components\TextInput::make('prezzo_totale')
                    ->numeric(),
                Forms\Components\TextInput::make('note')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('comanda.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prodotto.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantita')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prezzo_unitario')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prezzo_totale')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->searchable(),
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
            'index' => Pages\ListComandaDettaglios::route('/'),
            'create' => Pages\CreateComandaDettaglio::route('/create'),
            'edit' => Pages\EditComandaDettaglio::route('/{record}/edit'),
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
