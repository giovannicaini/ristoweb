<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\EventoResource\Pages\ListEventos;
use App\Filament\Resources\EventoResource\Pages\CreateEvento;
use App\Filament\Resources\EventoResource\Pages\EditEvento;
use App\Filament\Resources\EventoResource\Pages;
use App\Filament\Resources\EventoResource\RelationManagers;
use App\Models\Evento;
use Filament\Actions\Modal\Actions\Action;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Mail;

class EventoResource extends Resource
{
    protected static ?string $model = Evento::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    public static function getPluralLabel(): ?string
    {
        return "Eventi";
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('data')
                    ->required(),
                TextInput::make('ip_stampante')
                    ->maxLength(255),
                TextInput::make('incasso_effettivo')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                TextInput::make('evento_master')
                    ->numeric(),
                Toggle::make('attivo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->searchable(),
                TextColumn::make('data')
                    ->date()
                    ->sortable(),
                TextColumn::make('ip_stampante')
                    ->searchable(),
                TextColumn::make('incasso_effettivo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('evento_master')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('attivo')
                    ->boolean(),
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
                EditAction::make()
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
            'index' => ListEventos::route('/'),
            'create' => CreateEvento::route('/create'),
            'edit' => EditEvento::route('/{record}/edit'),
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
