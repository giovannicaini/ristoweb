<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\ComandaResource\RelationManagers\ComandeDettagliRelationManager;
use App\Filament\Resources\ComandaResource\Pages\ListComandas;
use App\Filament\Resources\ComandaResource\Pages\CreateComanda;
use App\Filament\Resources\ComandaResource\Pages\EditComanda;
use App\Filament\Forms\Components\ProdottoSlider as ComponentsProdottoSlider;
use App\Filament\Resources\ComandaResource\Pages;
use App\Filament\Resources\ComandaResource\RelationManagers;
use App\Forms\Components\ProdottoSlider;
use App\Models\Categoria;
use App\Models\Comanda;
use DeepCopy\Matcher\PropertyTypeMatcher;
use Filament\Actions\Modal\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Contracts\HasAffixActions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

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
        return \Filament\Actions\Action::make('action')
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
                        \Filament\Actions\Action::make('copy')
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
                TextInput::make('su_conto')
                    ->numeric(),
                Select::make('conto_id')
                    ->relationship('conto', 'id'),
                TextInput::make('stato')
                    ->maxLength(255),
                TextInput::make('note')
                    ->maxLength(255),
            ]);
    }



    public static function createSchema()
    {
        $schema = [];
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
                            \Filament\Actions\Action::make('addValueProdotto' . $prodotto->id)
                                ->icon('heroicon-s-plus')
                                ->action(function (Set $set, Get $get) use ($prodotto) {
                                    $set('prodotto_' . $prodotto->id, $get('prodotto_' . $prodotto->id) + 1);
                                })
                                ->extraAttributes(['tabIndex' => -1])
                        )
                        ->prefixAction(
                            \Filament\Actions\Action::make('dimValueProdotto' . $prodotto->id)
                                ->icon('heroicon-s-minus')
                                ->action(function (Set $set, Get $get) use ($prodotto) {

                                    $set('prodotto_' . $prodotto->id, $get('prodotto_' . $prodotto->id) - 1 > 0 ? $get('prodotto_' . $prodotto->id) - 1 : '');
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
                            \Filament\Actions\Action::make('note_cucina')
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
    public function decrementValue()
    {
        dd("CIAO");
    }
    /*
    public $generateNewCode = ActionsAction::make('generateNewCode')
        ->action(
            fn(TextInput $component) => $component->state("prova")
        );
*/
    public function dimTavolo()
    {
        dd("CIAO");
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('evento.id')
                    ->numeric()
                    ->sortable(),
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
                TextColumn::make('totale')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pagato')
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
            ComandeDettagliRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComandas::route('/'),
            'create' => CreateComanda::route('/create'),
            'edit' => EditComanda::route('/{record}/edit'),
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
