<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostazioneResource\Pages;
use App\Filament\Resources\PostazioneResource\RelationManagers;
use App\Models\Postazione;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostazioneResource extends Resource
{
    protected static ?string $model = Postazione::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function getPluralLabel(): ?string
    {
        return "Postazioni";
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
                Forms\Components\Toggle::make('accoda_a_scontrino'),
                Forms\Components\Toggle::make('stampa_coperti'),
                Forms\Components\TextInput::make('ordine')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('stampante_id')
                    ->relationship('stampante', 'id')
                    ->required(),
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
                Tables\Columns\IconColumn::make('accoda_a_scontrino')
                    ->boolean(),
                Tables\Columns\IconColumn::make('stampa_coperti')
                    ->boolean(),
                Tables\Columns\TextColumn::make('ordine')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stampante.id')
                    ->numeric()
                    ->sortable(),
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
            RelationManagers\CategorieRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPostaziones::route('/'),
            'create' => Pages\CreatePostazione::route('/create'),
            'edit' => Pages\EditPostazione::route('/{record}/edit'),
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
