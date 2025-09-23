<?php

namespace App\Filament\Cassa\Resources;

use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Cassa\Resources\ComandaResource\RelationManagers\ComandeDettagliRelationManager;
use App\Filament\Cassa\Resources\ComandaResource\RelationManagers\ComandePagamentiRelationManager;
use App\Filament\Cassa\Resources\ComandaResource\RelationManagers\ComandePostazioniRelationManager;
use App\Filament\Cassa\Resources\ComandaResource\Pages\ListComandas;
use App\Actions\StampaScontrino;
use App\Filament\Cassa\Resources\ComandaResource\Pages;
use App\Filament\Cassa\Resources\ComandaResource\RelationManagers;
use App\Models\Categoria;
use App\Models\Comanda;
use Faker\Core\Number;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number as SupportNumber;

class ComandaResource extends Resource
{
    protected static ?string $model = Comanda::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    public static function getPluralLabel(): ?string
    {
        return "Comande";
    }

    public static function action2()
    {
        return Action::make('action')
            ->icon('heroicon-m-minus')
            ->action(
                fn(TextInput $component) => dd($component)
            );
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('evento_id')
                    ->relationship('evento', 'id')
                    ->required(),
                TextInput::make('n_ordine')
                    ->required()
                    ->numeric(),
                TextInput::make('nominativo')
                    ->maxLength(255)
                    ->suffixAction(
                        Action::make('copy')
                            ->icon('heroicon-s-clipboard-document-check')
                            ->action(function (Set $set) {
                                $set('n_ordine', 2);
                            })
                    ),
                TextInput::make('tavolo')
                    ->maxLength(255),
                Toggle::make('asporto'),
                Select::make('cassiere_id')
                    ->relationship('cassiere', 'name')
                    ->required(),
                TextInput::make('cassa_id')
                    ->required()
                    ->numeric(),
                TextInput::make('totale')
                    ->numeric()
                    ->default(0.00),
                TextInput::make('pagato')
                    ->numeric(),
                TextInput::make('sconto')
                    ->numeric(),
                TextInput::make('buoni')
                    ->numeric(),
                Select::make('conto_id')
                    ->relationship('conto', 'id'),
                TextInput::make('su_conto')
                    ->numeric(),
                TextInput::make('stato')
                    ->maxLength(255),
                TextInput::make('note')
                    ->maxLength(255),
            ]);
    }

    public static function formTotali(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make("TOTALI E PAGAMENTO")->schema([
                    TextInput::make('totale_prodotti_senza_sconto')
                        ->label('Totale prodotti')
                        ->numeric()
                        ->default(0.00)
                        ->disabled()
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, string $state) {
                            $component->state(number_format($state, 2));
                        })
                        ->suffix('€'),
                    TextInput::make('totale_sconto_prodotti')
                        ->label('Sconto sui prodotti')
                        ->numeric()
                        ->default(0.00)
                        ->disabled()
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, string $state) {
                            $component->state(number_format($state, 2));
                        })
                        ->suffix('€'),
                    TextInput::make('totale_prodotti_con_sconto')
                        ->label('Subtotale')
                        ->numeric()
                        ->default(0.00)
                        ->disabled()
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, string $state) {
                            $component->state(number_format($state, 2));
                        })
                        ->suffix('€'),
                    TextInput::make('sconto')
                        ->label('Sconto sulla comanda')
                        ->numeric()
                        //->mask(RawJs::make('$money($input)'))
                        ->default(0.00)
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, $state) {
                            if ($state)
                                $component->state(number_format($state, 2));
                        })
                        ->live(debounce: 500)
                        ->afterStateUpdated(function (TextInput $component, Set $set, Get $get) {
                            $set('subtotale', number_format(floatval($get('totale_prodotti_con_sconto')) - floatval($get('sconto')) - floatval($get('buoni')), 2));
                            $set('subtotale2', number_format(floatval($get('subtotale')) - floatval($get('su_conto')), 2));
                            //if ($get('sconto'))
                            //  $component->state(number_format(floatval($get('sconto')), 2));
                        })
                        ->suffix('€'),
                    TextInput::make('buoni')
                        ->label('Buoni')
                        ->numeric()
                        //->mask(RawJs::make('$money($input)'))
                        ->default(0.00)
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, $state) {
                            if ($state)
                                $component->state(number_format($state, 2));
                        })
                        ->live(debounce: 500)
                        ->afterStateUpdated(function (TextInput $component, ?string $state, Set $set, Get $get) {
                            $set('subtotale', number_format(floatval($get('totale_prodotti_con_sconto')) - floatval($get('sconto')) - floatval($get('buoni')), 2));
                            $set('subtotale2', number_format(floatval($get('subtotale')) - floatval($get('su_conto')), 2));
                            // if ($get('buoni'))
                            //$component->state(number_format(floatval($get('buoni')), 2));
                        })
                        ->suffix('€'),
                    TextInput::make('subtotale')
                        ->label('Totale Da Pagare')
                        ->numeric()
                        ->default(0.00)
                        ->disabled()
                        ->live(debounce: 500)
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, Get $get) {
                            $component->state(number_format(floatval($get('totale_prodotti_con_sconto')) - floatval($get('sconto')) - floatval($get('buoni')), 2));
                        })
                        ->suffix('€'),

                    Select::make('conto_id')
                        ->label("Conto (volontari che pagano dopo)")
                        ->relationship('conto', 'nome')
                        ->native(false)
                        ->createOptionForm([
                            TextInput::make('nome')
                                ->required()
                        ])
                        ->editOptionForm([
                            TextInput::make('nome')
                                ->required()
                        ])
                        ->columnSpan(2)
                        ->live(),
                    TextInput::make('su_conto')
                        ->label('Importo sul conto')
                        ->numeric()
                        //->mask(RawJs::make('$money($input)'))
                        ->default(0.00)
                        ->hidden(fn(Get $get) => $get("conto_id") == null)
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, $state) {
                            if ($state)
                                $component->state(number_format($state, 2));
                        })
                        ->live(debounce: 500)
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            $set('subtotale2', number_format(floatval($get('subtotale')) - floatval($get('su_conto')), 2));
                            // if ($get('buoni'))
                            //$component->state(number_format(floatval($get('buoni')), 2));
                        })
                        ->afterStateUpdated(function (TextInput $component, ?string $state, Set $set, Get $get) {
                            $set('subtotale2', number_format(floatval($get('subtotale')) - floatval($get('su_conto')), 2));
                            // if ($get('buoni'))
                            //$component->state(number_format(floatval($get('buoni')), 2));
                        })
                        ->suffix('€')
                        ->columnSpan(2),
                    TextInput::make('subtotale2')
                        ->label('Totale Da Pagare')
                        ->numeric()
                        ->default(0.00)
                        ->disabled()
                        ->live(debounce: 500)
                        ->extraInputAttributes(["class" => "text-right"])
                        ->hidden(fn(Get $get) => $get("conto_id") == null)
                        ->afterStateHydrated(function (TextInput $component, Get $get) {
                            $component->state(number_format(floatval($get('subtotale')) - floatval($get('su_conto')), 2));
                        })
                        ->suffix('€')
                        ->columnStart(6),
                ])
                    ->columnSpan(2)
                    ->columns(6)
            ]);
    }


    public static function createSchema()
    {
        $schema = [];

        $schema[] = Section::make("DATI GENERALI COMANDA")
            ->schema([
                TextInput::make('n_ordine')
                    ->required()
                    ->numeric()
                    ->disabled(),
                TextInput::make('nominativo')
                    ->maxLength(255),
                TextInput::make('tavolo')
                    ->maxLength(255),
                Toggle::make('asporto'),
            ])
            ->columnSpan(1)
            ->columns(4)
            ->extraAttributes(["class" => "background-primary", "style" => "--c-600:var(--primary-600);"]);

        $categorie = Categoria::with('prodotti')->orderBy('ordine')->get();
        foreach ($categorie as $categoria) {
            $campi = [];
            foreach ($categoria->prodotti->sortBy('ordine') as $prodotto) {
                $campi[] = Group::make([
                    Placeholder::make('placeholder_nome_' . $prodotto->id)
                        ->content(function (Get $get) use ($prodotto) {
                            return new HtmlString('<span class=""><b>' . $prodotto->nome . '</b></span>');
                        })
                        ->label('')
                        ->extraAttributes(['class' => 'm-0'])
                        ->columnSpan(3),
                    TextInput::make('prodotto_' . $prodotto->id)
                        ->type('number')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffixAction(
                            Action::make('addValueProdotto' . $prodotto->id)
                                ->icon('heroicon-s-plus')
                                ->action(function (Set $set, Get $get) use ($prodotto) {
                                    $set('prodotto_' . $prodotto->id, intval($get('prodotto_' . $prodotto->id)) + 1);
                                })
                                ->extraAttributes(['tabIndex' => -1])
                        )
                        ->prefixAction(
                            Action::make('dimValueProdotto' . $prodotto->id)
                                ->icon('heroicon-s-minus')
                                ->action(function (Set $set, Get $get) use ($prodotto) {

                                    $set('prodotto_' . $prodotto->id, $get('prodotto_' . $prodotto->id) - 1 > 0 ? intval($get('prodotto_' . $prodotto->id)) - 1 : '');
                                })
                                ->disabled(function (Get $get) use ($prodotto) {
                                    return $get('prodotto_' . $prodotto->id) <= 0;
                                })
                                ->extraAttributes(['tabIndex' => -1])
                        )
                        ->extraAttributes(['class' => ''])
                        ->extraInputAttributes(['class' => 'text-center'])
                        ->label('')
                        ->columnSpan(2),
                    Placeholder::make('placeholder_prezzo_' . $prodotto->id)
                        ->content(function (Get $get) use ($prodotto) {
                            return new HtmlString('<span class=""><small>' . $prodotto->prezzo . ' € </small></span>');
                        })
                        ->label('')
                        ->key('placeholder_prezzo_' . $prodotto->id)
                        ->hintAction(
                            Action::make('note_cucina')
                                ->schema([
                                    Textarea::make('note_prodotto')
                                        ->label('Note per la cucina')
                                        ->required(),
                                ])
                                ->fillForm(fn(Get $get): array => [
                                    'note_prodotto' => $get('note_' . $prodotto->id)
                                ])
                                ->action(function (array $data, Set $set) use ($prodotto): void {
                                    $set('note_' . $prodotto->id, $data['note_prodotto']);
                                })
                                ->label('')
                                ->icon('heroicon-m-pencil-square')
                                ->requiresConfirmation()
                                ->extraAttributes(['tabIndex' => -1])
                        )
                        ->extraAttributes(['class' => 'text-left'])
                        ->columnSpan(1),
                    //->dehydrated(false),
                    Hidden::make('note_' . $prodotto->id),
                    Placeholder::make('placeholder_note_' . $prodotto->id)
                        ->content(function (Get $get) use ($prodotto) {
                            return new HtmlString('<span class="text-primary-400"><b>NOTE CUCINA: ' . $get('note_' . $prodotto->id) . '</b></span>');
                        })
                        ->label('')
                        //->label(new HtmlString('<span class="text-primary-400">Note Cucina</b>'))
                        ->visible(function (Get $get) use ($prodotto) {
                            return $get('note_' . $prodotto->id) != null;
                        })
                        ->extraAttributes(['class' => 'text-primary-400 label-primary-400'])
                        ->columnSpan(3)
                ])
                    ->columns(3);
            }
            $schema[] = Section::make($categoria->nome)
                ->schema($campi)
                ->columnSpan(1)
                ->columns(4);
        }
        return $schema;
    }

    public static function formComanda(Schema $schema): Schema
    {
        return $schema
            ->components(self::createSchema())
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('n_ordine')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nominativo')
                    ->searchable(),
                TextColumn::make('tavolo')
                    ->searchable(),
                IconColumn::make('asporto')
                    ->boolean(),
                TextColumn::make('cassiere.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cassa_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('totale_pagato')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('totale_da_pagare')
                    ->label('Da Pagare')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sconto')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('buoni')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('su_conto')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('conto.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stato')
                    ->searchable(),
                TextColumn::make('note')
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
                TrashedFilter::make(),
            ])
            ->recordActions([
                //Tables\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn(Model $record): string => route('filament.cassa.resources.comandas.comanda', ['record' => $record]),
            )
            ->defaultSort('created_at', 'desc');
    }
    public static function getRelations(): array
    {
        return [
            ComandeDettagliRelationManager::class,
            ComandePagamentiRelationManager::class,
            ComandePostazioniRelationManager::class

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComandas::route('/'),
            //'create' => Pages\CreateComanda::route('/create'),
            //'edit' => Pages\EditComanda::route('/{record}/edit'),
            'comanda' => Pages\Comanda::route('/{record}/comanda'),
            'gestione' => Pages\GestioneComanda::route('/{record_id}/gestione'),


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
