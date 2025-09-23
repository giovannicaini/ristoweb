<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\TipologiaPagamentoResource\Pages\ListTipologiaPagamentos;
use App\Filament\Resources\TipologiaPagamentoResource\Pages\CreateTipologiaPagamento;
use App\Filament\Resources\TipologiaPagamentoResource\Pages\EditTipologiaPagamento;
use App\Filament\Resources\TipologiaPagamentoResource\Pages;
use App\Filament\Resources\TipologiaPagamentoResource\RelationManagers;
use App\Models\TipologiaPagamento;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class TipologiaPagamentoResource extends Resource
{
    protected static ?string $model = TipologiaPagamento::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';


    public static function getPluralLabel(): ?string
    {
        return "Tipologia Pagamenti";
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required()
                    ->maxLength(255)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
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
            'index' => ListTipologiaPagamentos::route('/'),
            'create' => CreateTipologiaPagamento::route('/create'),
            'edit' => EditTipologiaPagamento::route('/{record}/edit'),
        ];
    }
}
