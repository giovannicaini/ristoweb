<?php

namespace App\Filament\Cassa\Resources;

use App\Actions\StampaScontrino;
use App\Filament\Cassa\Resources\ComandaResource\Pages;
use App\Filament\Cassa\Resources\ComandaResource\RelationManagers;
use App\Models\Categoria;
use App\Models\Comanda;
use Faker\Core\Number;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as ActionsAction;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getPluralLabel(): ?string
    {
        return "Cassa";
    }

    public static function action2()
    {
        return ActionsAction::make('action')
            ->icon('heroicon-m-minus')
            ->action(
                fn(TextInput $component) => dd($component)
            );
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('evento_id')
                    ->relationship('evento', 'id')
                    ->required(),
                Forms\Components\TextInput::make('n_ordine')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nominativo')
                    ->maxLength(255)
                    ->suffixAction(
                        ActionsAction::make('copy')
                            ->icon('heroicon-s-clipboard-document-check')
                            ->action(function (Set $set) {
                                $set('n_ordine', 2);
                            })
                    ),
                Forms\Components\TextInput::make('tavolo')
                    ->maxLength(255),
                Forms\Components\Toggle::make('asporto'),
                Forms\Components\Select::make('cassiere_id')
                    ->relationship('cassiere', 'name')
                    ->required(),
                Forms\Components\TextInput::make('cassa_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('totale')
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('pagato')
                    ->numeric(),
                Forms\Components\TextInput::make('sconto')
                    ->numeric(),
                Forms\Components\TextInput::make('buoni')
                    ->numeric(),
                Forms\Components\Select::make('conto_id')
                    ->relationship('conto', 'id'),
                Forms\Components\TextInput::make('su_conto')
                    ->numeric(),
                Forms\Components\TextInput::make('stato')
                    ->maxLength(255),
                Forms\Components\TextInput::make('note')
                    ->maxLength(255),
            ]);
    }

    public static function formTotali(Form $form): Form
    {
        return $form
            ->schema([
                Section::make("TOTALI E PAGAMENTO")->schema([
                    Forms\Components\TextInput::make('totale_prodotti_senza_sconto')
                        ->label('Totale prodotti')
                        ->numeric()
                        ->default(0.00)
                        ->disabled()
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, string $state) {
                            $component->state(number_format($state, 2));
                        })
                        ->suffix('€'),
                    Forms\Components\TextInput::make('totale_sconto_prodotti')
                        ->label('Sconto sui prodotti')
                        ->numeric()
                        ->default(0.00)
                        ->disabled()
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, string $state) {
                            $component->state(number_format($state, 2));
                        })
                        ->suffix('€'),
                    Forms\Components\TextInput::make('totale_prodotti_con_sconto')
                        ->label('Subtotale')
                        ->numeric()
                        ->default(0.00)
                        ->disabled()
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, string $state) {
                            $component->state(number_format($state, 2));
                        })
                        ->suffix('€'),
                    Forms\Components\TextInput::make('sconto')
                        ->label('Sconto sulla comanda')
                        ->numeric()
                        //->mask(RawJs::make('$money($input)'))
                        ->default(0.00)
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, $state) {
                            if ($state)
                                $component->state(number_format($state, 2));
                        })
                        ->live()
                        ->afterStateUpdated(function (TextInput $component, Set $set, Get $get) {
                            $set('subtotale', number_format(floatval($get('totale_prodotti_con_sconto')) - floatval($get('sconto')) - floatval($get('buoni')), 2));
                            //if ($get('sconto'))
                            //  $component->state(number_format(floatval($get('sconto')), 2));
                        })
                        ->suffix('€'),
                    Forms\Components\TextInput::make('buoni')
                        ->label('Buoni')
                        ->numeric()
                        //->mask(RawJs::make('$money($input)'))
                        ->default(0.00)
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, $state) {
                            if ($state)
                                $component->state(number_format($state, 2));
                        })
                        ->live()
                        ->afterStateUpdated(function (TextInput $component, ?string $state, Set $set, Get $get) {
                            $set('subtotale', number_format(floatval($get('totale_prodotti_con_sconto')) - floatval($get('sconto')) - floatval($get('buoni')), 2));
                            // if ($get('buoni'))
                            //$component->state(number_format(floatval($get('buoni')), 2));
                        })
                        ->suffix('€'),
                    Forms\Components\TextInput::make('subtotale')
                        ->label('Totale Da Pagare')
                        ->numeric()
                        ->default(0.00)
                        ->disabled()
                        ->live()
                        ->extraInputAttributes(["class" => "text-right"])
                        ->afterStateHydrated(function (TextInput $component, Get $get) {
                            $component->state(number_format(floatval($get('totale_prodotti_con_sconto')) - floatval($get('sconto')), 2));
                        })
                        ->suffix('€'),

                    Forms\Components\Select::make('conto_id')
                        ->relationship('conto', 'nome')
                        ->createOptionForm([
                            Forms\Components\TextInput::make('nome')
                                ->required()
                        ]),
                    Forms\Components\TextInput::make('su_conto')
                        ->label("Importo da caricare sul conto")
                        ->numeric(),



                ])
                    ->columnSpan(2)
                    ->columns(6),
                Actions::make([
                    ActionsAction::make('stampaAll')
                        ->label('Stampa Tutto')
                        //->color(950)
                        ->requiresConfirmation()
                        ->action(function (Model $record) {
                            StampaScontrino::run($record, 'tutto');
                        }),
                ])

            ]);
    }


    public static function createSchema()
    {
        $schema = [];

        $schema[] = Section::make("DATI GENERALI COMANDA")
            ->schema([
                Forms\Components\TextInput::make('n_ordine')
                    ->required()
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('nominativo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('tavolo')
                    ->maxLength(255),
                Forms\Components\Toggle::make('asporto'),
            ])
            ->columnSpan(1)
            ->columns(4)
            ->extraAttributes(["class" => "background-primary", "style" => "--c-600:var(--primary-600);"]);

        $categorie = Categoria::get();
        foreach ($categorie as $categoria) {
            $campi = [];
            foreach ($categoria->prodotti as $prodotto) {
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
                            ActionsAction::make('addValueProdotto' . $prodotto->id)
                                ->icon('heroicon-s-plus')
                                ->action(function (Set $set, Get $get) use ($prodotto) {
                                    $set('prodotto_' . $prodotto->id, intval($get('prodotto_' . $prodotto->id)) + 1);
                                })
                                ->extraAttributes(['tabIndex' => -1])
                        )
                        ->prefixAction(
                            ActionsAction::make('dimValueProdotto' . $prodotto->id)
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
                            ActionsAction::make('note_cucina')
                                ->form([
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

    public static function formComanda(Form $form): Form
    {
        return $form
            ->schema(self::createSchema())
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('n_ordine')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominativo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tavolo')
                    ->searchable(),
                Tables\Columns\IconColumn::make('asporto')
                    ->boolean(),
                Tables\Columns\TextColumn::make('cassiere.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cassa_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('totale')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pagato')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sconto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('buoni')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('su_conto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('conto.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stato')
                    ->searchable(),
                Tables\Columns\TextColumn::make('note')
                    ->searchable(),
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
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            RelationManagers\ComandeDettagliRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComandas::route('/'),
            'create' => Pages\CreateComanda::route('/create'),
            //'edit' => Pages\EditComanda::route('/{record}/edit'),
            'comanda' => Pages\Comanda::route('/{record}/comanda'),

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
