<?php

namespace App\Filament\Resources\ComandaResource\RelationManagers;

use Filament\Forms;
use Livewire\Component;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComandeDettagliRelationManager extends RelationManager
{
    protected static string $relationship = 'comande_dettagli';

    protected $listeners = ['refreshRelation' => '$refresh'];


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('prodotto_id')
                    ->relationship('prodotto', 'nome_breve')
                    ->required(),
                Forms\Components\TextInput::make('quantita')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('sconto_unitario')
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\TextInput::make('note')
                    ->label("Note per la cucina")
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('prodotto.nome_breve')
            ->paginated(false)
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('prodotto.nome_breve')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantita')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('prodotto.prezzo')
                    ->money('EUR')
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('sconto_unitario')
                    ->money('EUR')
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('prezzo_totale')
                    ->getStateUsing(function(Model $record) {
                        // return whatever you need to show
                        return ($record->prodotto->prezzo - $record->sconto_unitario)*$record->quantita;
                    })
                    ->money('EUR')
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('note')
                    ->label("Note Cucina")
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->after(function (Component $livewire) { 
                    $livewire->dispatch('refreshComanda');
                }),
                Tables\Actions\DeleteAction::make()
                ->after(function (Component $livewire) { 
                    $livewire->dispatch('refreshComanda');
                }),
                Tables\Actions\ForceDeleteAction::make()
                ->after(function (Component $livewire) { 
                    $livewire->dispatch('refreshComanda');
                }),
                Tables\Actions\RestoreAction::make()
                ->after(function (Component $livewire) { 
                    $livewire->dispatch('refreshComanda');
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ->after(function (Component $livewire) { 
                        $livewire->dispatch('refreshComanda');
                    }),
                    Tables\Actions\ForceDeleteBulkAction::make()
                    ->after(function (Component $livewire) { 
                        $livewire->dispatch('refreshComanda');
                    }),
                    Tables\Actions\RestoreBulkAction::make()
                    ->after(function (Component $livewire) { 
                        $livewire->dispatch('refreshComanda');
                    }),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }
}
