<?php

namespace App\Filament\Cassa\Resources\ComandaResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Closure;
use Filament\Forms;
use Livewire\Component;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class ComandeDettagliRelationManager extends RelationManager
{
    protected static string $relationship = 'comande_dettagli';

    protected static ?string $title = 'Riepilogo prodotti inseriti in comanda';

    protected $listeners = ['refreshRelation' => '$refresh'];


    public function form(Schema $schema): Schema
    {
        $ids = $this->ownerRecord->comande_dettagli->pluck('prodotto_id')->toArray();
        return $schema
            ->components([
                Select::make('prodotto_id')
                    ->relationship(
                        name: 'prodotto',
                        titleAttribute: 'nome',
                        modifyQueryUsing: fn(Builder $query) => $query->whereNotIn('id', $ids)
                    )
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('quantita')
                    ->required()
                    ->numeric(),
                TextInput::make('sconto_unitario')
                    ->numeric()
                    ->prefix('€'),
                TextInput::make('note')
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
                TextColumn::make('quantita')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('prodotto.nome_breve')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('prodotto.prezzo')
                    ->money('EUR')
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('sconto_unitario')
                    ->money('EUR')
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('prezzo_totale')
                    ->getStateUsing(function (Model $record) {
                        // return whatever you need to show
                        return ($record->prodotto->prezzo - $record->sconto_unitario) * $record->quantita;
                    })
                    ->money('EUR')
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('note')
                    ->label("Note Cucina")
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
                DeleteAction::make()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
                ForceDeleteAction::make()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
                RestoreAction::make()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshComanda');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function (Component $livewire) {
                            $livewire->dispatch('refreshComanda');
                        }),
                    ForceDeleteBulkAction::make()
                        ->after(function (Component $livewire) {
                            $livewire->dispatch('refreshComanda');
                        }),
                    RestoreBulkAction::make()
                        ->after(function (Component $livewire) {
                            $livewire->dispatch('refreshComanda');
                        }),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }

    protected function configureCreateAction(CreateAction $action): void
    {
        $action
            ->authorize(static fn(RelationManager $livewire): bool => (! $livewire->isReadOnly()) && $livewire->canCreate())
            ->schema(fn(Schema $schema): Schema => $this->form($schema->columns(2)))
            ->modalDescription('Inserisci nuovo prodotto nella comanda (NB non è possibile inserire prodotti già in comanda)');
    }
}
