<?php

namespace App\Filament\Cassa\Resources\ComandaResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Actions\StampaScontrino;
use App\Models\TipologiaPagamento;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Livewire\Component;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;
use Illuminate\View\View as ViewView;

class ComandePagamentiRelationManager extends RelationManager
{
    protected static string $relationship = 'pagamenti';

    protected static ?string $title = 'Pagamenti associati alla Comanda';

    protected $listeners = ['refreshRelation' => '$refresh'];


    public function form(Schema $schema): Schema
    {
        $ids = $this->ownerRecord->comande_dettagli->pluck('prodotto_id')->toArray();
        return $schema
            ->components([
                TextInput::make('importo')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Select::make('tipologia_pagamento_id')
                    ->relationship('tipologia_pagamento', 'nome')
                    ->required(),
            ]);
    }
    public function table(Table $table): Table
    {
        $ownerRecord = $this->ownerRecord;
        return $table
            ->recordTitleAttribute('tipologia_pagamento.nome')
            ->paginated(false)
            ->striped()
            ->columns([
                TextColumn::make('tipologia_pagamento.nome')
                    ->sortable(),
                TextColumn::make('importo')
                    ->money('EUR')
                    ->sortable()
                    ->alignRight()
                    ->summarize(Sum::make()->money('EUR')->label('Totale Pagato')->extraAttributes(["class" => "text-primary-600"])),
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
                TrashedFilter::make()
            ])
            ->headerActions([
                CreateAction::make()->label("Inserisci nuovo pagamento [F1]")
                    ->keyBindings(['f1'])
                    ->schema([
                        Group::make([
                            TextInput::make('importo')
                                ->required()
                                ->numeric()
                                ->prefix('€')
                                ->default($ownerRecord->totale_da_pagare)
                                ->columnSpan(1),
                            Select::make('tipologia_pagamento_id')
                                ->relationship('tipologia_pagamento', 'nome')
                                ->default(TipologiaPagamento::where('nome', 'Contanti')->first()->id)
                                ->required()
                                ->live()
                                ->columnSpan(1),
                            TextInput::make('contanti')
                                ->label("Contanti Dati (se si vuole calcolare il resto)")
                                ->dehydrated(false)
                                ->numeric()
                                ->hidden(fn(Get $get) => $get("tipologia_pagamento_id") != TipologiaPagamento::where('nome', 'Contanti')->first()->id)
                                ->prefix('€')
                                ->live(debounce: 500)
                                ->afterStateUpdated(fn(Get $get, Set $set) => $set('resto', number_format(floatval($get('contanti')) - floatval($get('importo')), 2)))
                                ->columnSpan(1),
                            TextInput::make('resto')
                                ->dehydrated(false)
                                ->disabled()
                                ->numeric()
                                ->hidden(fn(Get $get) => $get("tipologia_pagamento_id") != TipologiaPagamento::where('nome', 'Contanti')->first()->id)
                                ->prefix('€')
                                ->columnSpan(1),
                        ])->columns(2)
                    ]),
                Action::make()->make('stampaAll')
                    ->label('Stampa Tutto [F2]')
                    ->modalHeading('Stampa Scontrino alla Cassa e Comande nelle varie postazioni')
                    ->requiresConfirmation()
                    ->action(function () use ($ownerRecord) {
                        StampaScontrino::run($ownerRecord, 'tutto');
                    })
                    ->keyBindings(['f2'])
                    ->modalIcon('heroicon-o-printer')
                    ->modalCancelAction(false)
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
            ]))
            ->contentFooter(function () use ($ownerRecord) {
                return view('footer-pagamenti', ['comanda' => $ownerRecord]);
            });
    }

    protected function configureCreateAction(CreateAction $action): void
    {
        $action
            ->authorize(static fn(RelationManager $livewire): bool => (! $livewire->isReadOnly()) && $livewire->canCreate())
            ->schema(fn(Schema $schema): Schema => $this->form($schema->columns(2)))
            ->modalDescription('Associa nuovo pagamento alla comanda');
    }
}
