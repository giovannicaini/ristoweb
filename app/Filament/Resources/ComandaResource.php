<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComandaResource\Pages;
use App\Filament\Resources\ComandaResource\RelationManagers;
use App\Models\Comanda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComandaResource extends Resource
{
    protected static ?string $model = Comanda::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getPluralLabel(): ?string
    {
        return "Comande";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('evento_id')
                    ->relationship('evento', 'id')
                    ->required(),
                Forms\Components\TextInput::make('n_ordine')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nominativo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('tavolo')
                    ->maxLength(255),
                Forms\Components\Toggle::make('asporto'),
                Forms\Components\Select::make('cassiere_id')
                    ->relationship('cassiere', 'name')
                    ->required(),
                Forms\Components\TextInput::make('cassa_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('totale')
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('pagato')
                    ->numeric(),
                Forms\Components\TextInput::make('sconto')
                    ->numeric(),
                Forms\Components\TextInput::make('buoni')
                    ->numeric(),
                Forms\Components\TextInput::make('su_conto')
                    ->numeric(),
                Forms\Components\Select::make('conto_id')
                    ->relationship('conto', 'id'),
                Forms\Components\TextInput::make('stato')
                    ->maxLength(255),
                Forms\Components\TextInput::make('note')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('evento.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('n_ordine')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominativo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tavolo')
                    ->searchable(),
                Tables\Columns\IconColumn::make('asporto')
                    ->boolean(),
                Tables\Columns\TextColumn::make('cassiere.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cassa_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('totale')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pagato')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sconto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('buoni')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('su_conto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('conto.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stato')
                    ->searchable(),
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
            RelationManagers\ComandeDettagliRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComandas::route('/'),
            'create' => Pages\CreateComanda::route('/create'),
            'edit' => Pages\EditComanda::route('/{record}/edit'),
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
