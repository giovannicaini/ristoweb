<?php

namespace App\Filament\Resources\ComandaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComandeDettagliRelationManager extends RelationManager
{
    protected static string $relationship = 'comande_dettagli';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('prodotto_id')
                    ->relationship('prodotto', 'id')
                    ->required(),
                Forms\Components\TextInput::make('quantita')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('prezzo_unitario')
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\TextInput::make('prezzo_totale')
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\TextInput::make('note')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('prodotto.nome_breve')
            ->columns([
                Tables\Columns\TextColumn::make('prodotto.nome_breve')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantita')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prezzo_unitario')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('prezzo_totale')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('note')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
