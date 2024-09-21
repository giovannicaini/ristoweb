<?php

namespace App\Filament\Cassa\Resources\ComandaResource\RelationManagers;

use App\Actions\StampaScontrino;
use App\Models\TipologiaPagamento;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Livewire\Component;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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


    public function form(Form $form): Form
    {
        $ids = $this->ownerRecord->comande_dettagli->pluck('prodotto_id')->toArray();
        return $form
            ->schema([
                Forms\Components\TextInput::make('importo')
                    ->required()
                    ->numeric()
                    ->prefix('€'),
                Forms\Components\Select::make('tipologia_pagamento_id')
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
                Tables\Columns\TextColumn::make('tipologia_pagamento.nome')
                    ->sortable(),
                Tables\Columns\TextColumn::make('importo')
                    ->money('EUR')
                    ->sortable()
                    ->alignRight()
                    ->summarize(Sum::make()->money('EUR')->label('Totale Pagato')->extraAttributes(["class" => "text-primary-600"])),
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
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label("Inserisci nuovo pagamento [F1]")
                    ->keyBindings(['f1'])
                    ->form([
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('importo')
                                ->required()
                                ->numeric()
                                ->prefix('€')
                                ->default($ownerRecord->totale_da_pagare)
                                ->columnSpan(1),
                            Forms\Components\Select::make('tipologia_pagamento_id')
                                ->relationship('tipologia_pagamento', 'nome')
                                ->default(TipologiaPagamento::where('nome', 'Contanti')->first()->id)
                                ->required()
                                ->live()
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('contanti')
                                ->label("Contanti Dati (se si vuole calcolare il resto)")
                                ->dehydrated(false)
                                ->numeric()
                                ->hidden(fn(Get $get) => $get("tipologia_pagamento_id") != TipologiaPagamento::where('nome', 'Contanti')->first()->id)
                                ->prefix('€')
                                ->live()
                                ->afterStateUpdated(fn(Get $get, Set $set) => $set('resto', number_format(floatval($get('contanti')) - floatval($get('importo')), 2)))
                                ->columnSpan(1),
                            Forms\Components\TextInput::make('resto')
                                ->dehydrated(false)
                                ->disabled()
                                ->numeric()
                                ->hidden(fn(Get $get) => $get("tipologia_pagamento_id") != TipologiaPagamento::where('nome', 'Contanti')->first()->id)
                                ->prefix('€')
                                ->columnSpan(1),
                        ])->columns(2)
                    ]),
                Tables\Actions\Action::make()->make('stampaAll')
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
            ]))
            ->contentFooter(function () use ($ownerRecord) {
                return view('footer-pagamenti', ['comanda' => $ownerRecord]);
            });
    }

    protected function configureCreateAction(Tables\Actions\CreateAction $action): void
    {
        $action
            ->authorize(static fn(RelationManager $livewire): bool => (! $livewire->isReadOnly()) && $livewire->canCreate())
            ->form(fn(Form $form): Form => $this->form($form->columns(2)))
            ->modalDescription('Associa nuovo pagamento alla comanda');
    }
}
