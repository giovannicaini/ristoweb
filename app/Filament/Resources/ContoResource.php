<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\ContoResource\Pages\ListContos;
use App\Filament\Resources\ContoResource\Pages\CreateConto;
use App\Filament\Resources\ContoResource\Pages\EditConto;
use App\Filament\Resources\ContoResource\Pages;
use App\Filament\Resources\ContoResource\RelationManagers;
use App\Models\Conto;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContoResource extends Resource
{
    protected static ?string $model = Conto::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';

    public static function getPluralLabel(): ?string
    {
        return "Conti";
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Select::make('evento_id')
                    ->relationship('evento', 'id')
                    ->required(),
                TextInput::make('numero_persone')
                    ->numeric(),
                TextInput::make('buono_a_testa')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->searchable(),
                TextColumn::make('evento.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('numero_persone')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('buono_a_testa')
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
            'index' => ListContos::route('/'),
            'create' => CreateConto::route('/create'),
            'edit' => EditConto::route('/{record}/edit'),
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
