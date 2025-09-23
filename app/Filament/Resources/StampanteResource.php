<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\StampanteResource\Pages\ListStampantes;
use App\Filament\Resources\StampanteResource\Pages\CreateStampante;
use App\Filament\Resources\StampanteResource\Pages\EditStampante;
use App\Filament\Resources\StampanteResource\Pages;
use App\Filament\Resources\StampanteResource\RelationManagers;
use App\Models\Stampante;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StampanteResource extends Resource
{
    protected static ?string $model = Stampante::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-printer';

    public static function getPluralLabel(): ?string
    {
        return "Stampanti";
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ip')
                    ->required()
                    ->maxLength(255),
                TextInput::make('descrizione')
                    ->required()
                    ->maxLength(255),
                TextInput::make('codice_euro')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ip')
                    ->searchable(),
                TextColumn::make('descrizione')
                    ->searchable(),
                TextColumn::make('codice_euro')
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
            'index' => ListStampantes::route('/'),
            'create' => CreateStampante::route('/create'),
            'edit' => EditStampante::route('/{record}/edit'),
        ];
    }
}
