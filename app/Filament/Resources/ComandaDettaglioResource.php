<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\ComandaDettaglioResource\Pages\ListComandaDettaglios;
use App\Filament\Resources\ComandaDettaglioResource\Pages\CreateComandaDettaglio;
use App\Filament\Resources\ComandaDettaglioResource\Pages\EditComandaDettaglio;
use App\Filament\Resources\ComandaDettaglioResource\Pages;
use App\Filament\Resources\ComandaDettaglioResource\RelationManagers;
use App\Models\ComandaDettaglio;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComandaDettaglioResource extends Resource
{
    protected static ?string $model = ComandaDettaglio::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-queue-list';

    public static function getPluralLabel(): ?string
    {
        return "Comande Dettagli";
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('comanda_id')
                    ->relationship('comanda', 'id')
                    ->required(),
                Select::make('prodotto_id')
                    ->relationship('prodotto', 'id')
                    ->required(),
                TextInput::make('quantita')
                    ->required()
                    ->numeric(),
                TextInput::make('prezzo_unitario')
                    ->numeric(),
                TextInput::make('prezzo_totale')
                    ->numeric(),
                TextInput::make('note')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('comanda.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('prodotto.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantita')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('prezzo_unitario')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('prezzo_totale')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('note')
                    ->searchable(),
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
            'index' => ListComandaDettaglios::route('/'),
            'create' => CreateComandaDettaglio::route('/create'),
            'edit' => EditComandaDettaglio::route('/{record}/edit'),
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
