<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipologiaPagamentoResource\Pages;
use App\Filament\Resources\TipologiaPagamentoResource\RelationManagers;
use App\Models\TipologiaPagamento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class TipologiaPagamentoResource extends Resource
{
    protected static ?string $model = TipologiaPagamento::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';


    public static function getPluralLabel(): ?string
    {
        return "Tipologia Pagamenti";
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTipologiaPagamentos::route('/'),
            'create' => Pages\CreateTipologiaPagamento::route('/create'),
            'edit' => Pages\EditTipologiaPagamento::route('/{record}/edit'),
        ];
    }
}
