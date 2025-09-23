<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\PostazioneResource\RelationManagers\CategorieRelationManager;
use App\Filament\Resources\PostazioneResource\Pages\ListPostaziones;
use App\Filament\Resources\PostazioneResource\Pages\CreatePostazione;
use App\Filament\Resources\PostazioneResource\Pages\EditPostazione;
use App\Filament\Resources\PostazioneResource\Pages;
use App\Filament\Resources\PostazioneResource\RelationManagers;
use App\Models\Postazione;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostazioneResource extends Resource
{
    protected static ?string $model = Postazione::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-flag';


    public static function getPluralLabel(): ?string
    {
        return "Postazioni";
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
                Toggle::make('accoda_a_scontrino'),
                Toggle::make('stampa_coperti'),
                TextInput::make('ordine')
                    ->required()
                    ->numeric(),
                Select::make('stampante_id')
                    ->relationship('stampante', 'id')
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
                IconColumn::make('accoda_a_scontrino')
                    ->boolean(),
                IconColumn::make('stampa_coperti')
                    ->boolean(),
                TextColumn::make('ordine')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stampante.id')
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
            CategorieRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostaziones::route('/'),
            'create' => CreatePostazione::route('/create'),
            'edit' => EditPostazione::route('/{record}/edit'),
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
