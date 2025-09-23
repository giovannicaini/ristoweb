<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\PagamentoResource\Pages\ListPagamentos;
use App\Filament\Resources\PagamentoResource\Pages\CreatePagamento;
use App\Filament\Resources\PagamentoResource\Pages\EditPagamento;
use App\Filament\Resources\PagamentoResource\Pages;
use App\Filament\Resources\PagamentoResource\RelationManagers;
use App\Models\Pagamento;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PagamentoResource extends Resource
{
    protected static ?string $model = Pagamento::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';


    public static function getPluralLabel(): ?string
    {
        return "Pagamenti";
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('comanda_id')
                    ->relationship('comanda', 'id')
                    ->required(),
                TextInput::make('importo')
                    ->required()
                    ->numeric(),
                Select::make('evento_id')
                    ->relationship('evento', 'id')
                    ->required(),
                Select::make('tipologia_pagamento_id')
                    ->relationship('tipologia_pagamento', 'id')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('comanda.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('importo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('evento.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tipologia_pagamento.id')
                    ->numeric()
                    ->sortable(),
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
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListPagamentos::route('/'),
            'create' => CreatePagamento::route('/create'),
            'edit' => EditPagamento::route('/{record}/edit'),
        ];
    }
}
