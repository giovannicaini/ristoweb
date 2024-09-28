<?php

namespace App\Filament\Cassa\Resources\ComandaResource\Pages;

use App\Actions\StampaScontrino;
use App\Actions\SyncComandePostazioni;
use App\Filament\Cassa\Loggers\ComandaLogger;
use App\Filament\Cassa\Resources\ComandaResource;
use App\Models\Comanda as ModelsComanda;
use App\Models\ComandaDettaglio;
use App\Models\Prodotto;
use Closure;
use Filament\Actions;
use Filament\Actions\Action as FilamentActionsAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\Modal\Actions\Action;
use Filament\Forms\Components\Actions\Action as ActionsAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\TextInput;

use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Actions\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use InvalidArgumentException;
use Livewire\Attributes\On;
use Lorisleiva\Actions\Action as LorisleivaActionsAction;
use Noxo\FilamentActivityLog\Extensions\LogEditRecord;
use Throwable;

use function Filament\Support\is_app_url;

class Comanda extends EditRecord
{
    //use LogEditRecord; // <--- here

    public ?array $dataTotali = [];
    public function getContentTabLabel(): ?string
    {
        return "Modifica Comanda";
    }
    protected static string $resource = ComandaResource::class;
    protected static string $layout = 'no-menu';
    public static string $view = 'comanda';
    protected $listeners = ['refreshComanda' => '$refresh'];

    #[On('refreshComanda')]
    public function refresh(): void
    {
        $this->fillForm();
    }
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }
    protected function getForms(): array
    {
        return [
            'form' => $this->form(static::getResource()::formComanda(
                $this->makeForm()
                    ->operation('edit')
                    ->model($this->getRecord())
                    ->statePath($this->getFormStatePath())
                    ->columns($this->hasInlineLabels() ? 1 : 2)
                    ->inlineLabel($this->hasInlineLabels()),
            )),
            'formTotali' => $this->form(static::getResource()::formTotali(
                $this->makeForm()
                    ->operation('edit')
                    ->model($this->getRecord())
                    ->statePath('dataTotali')
                    ->columns($this->hasInlineLabels() ? 1 : 2)
                    ->inlineLabel($this->hasInlineLabels()),
            )),
        ];
    }

    public function getFormStatePath(): ?string
    {
        return 'data';
    }
    public function getFormTotaliStatePath(): ?string
    {
        return 'dataTotali';
    }

    protected function fillForm(): void
    {
        /** @internal Read the DocBlock above the following method. */
        $qta_prodotti = [];
        foreach (Prodotto::with('comande_dettagli')->get() as $prodotto) {

            $filtered = $prodotto->comande_dettagli->filter(function ($comanda_dettaglio) {
                return $comanda_dettaglio->comanda_id == $this->getRecord()->id;
            });
            //$test = ComandaDettaglio::where('comanda_id', $this->getRecord()->id)->where('prodotto_id', $prodotto->id)->first();
            if ($filtered->first()) {
                $qta_prodotti['prodotto_' . $prodotto->id] = $filtered->first()->quantita;
                if ($filtered->first()->note)
                    $qta_prodotti['note_' . $prodotto->id] = $filtered->first()->note;
            }
        }
        $this->fillFormWithDataAndCallHooks($this->getRecord(), $qta_prodotti);
        $this->fillFormTotaliWithDataAndCallHooks($this->getRecord());
    }

    /**
     * @internal Never override or call this method. If you completely override `fillForm()`, copy the contents of this method into your override.
     *
     * @param  array<string, mixed>  $extraData
     */
    protected function fillFormWithDataAndCallHooks(Model $record, array $extraData = []): void
    {
        $this->callHook('beforeFill');

        $data = $this->mutateFormDataBeforeFill([
            ...$record->attributesToArray(),
            ...$extraData,
        ]);

        $this->form->fill($data);

        $this->callHook('afterFill');
    }
    protected function fillFormTotaliWithDataAndCallHooks(Model $record, array $extraData = []): void
    {
        $this->callHook('beforeFillTotali');

        $dataTotali = $this->mutateFormDataBeforeFill([
            ...$record->attributesToArray(),
            ...$extraData,
        ]);
        $this->formTotali->fill($dataTotali);

        $this->callHook('afterFillTotali');
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $dettagli = [];
        $data2 = [];

        foreach ($data as $campo => $valore) {
            $arr = explode('_', $campo);
            if ($valore && $arr[0] == "prodotto") {
                $dettaglio = [
                    "prodotto_id" => $arr[1],
                    "quantita" => $valore
                ];
                if ($data['note_' . $arr[1]])
                    $dettaglio["note"] = $data['note_' . $arr[1]];
                $dettagli[] = $dettaglio;
            }
        }
        $data2['comande_dettagli'] = $dettagli;
        $data2['nominativo'] = $data['nominativo'];
        $data2['tavolo'] = $data['tavolo'];
        $data2['asporto'] = $data['asporto'];
        return $data2;
    }

    protected function mutateFormTotaliDataBeforeSave(array $data): array
    {
        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $new_prodotti_id = [];
        $old_prodotti_id = ComandaDettaglio::where('comanda_id', $record->id)->get()->pluck('prodotto_id')->toArray();
        foreach ($data["comande_dettagli"] as $dettaglio) {
            $new_prodotti_id[] = $dettaglio["prodotto_id"];
            $dettaglio_ok = ComandaDettaglio::where('comanda_id', $record->id)->where('prodotto_id', $dettaglio['prodotto_id'])->first();
            if (!$dettaglio_ok) {
                $dettaglio_ok = new ComandaDettaglio();
                $dettaglio_ok->comanda_id = $record->id;
                $dettaglio_ok->prodotto_id = $dettaglio['prodotto_id'];
                $dettaglio_ok->prezzo_unitario = Prodotto::find($dettaglio['prodotto_id'])->prezzo;
            }
            $dettaglio_ok->quantita = $dettaglio["quantita"] ?? null;
            $dettaglio_ok->note = $dettaglio["note"] ?? null;
            $dettaglio_ok->save();
        }
        $delete = array_diff($old_prodotti_id, $new_prodotti_id);
        ComandaDettaglio::where('comanda_id', $record->id)->whereIn('prodotto_id', $delete)->delete();
        //$record->update($data);
        $record->nominativo = $data['nominativo'];
        $record->tavolo = $data['tavolo'];
        $record->asporto = $data['asporto'];
        $record->save();
        $this->activeRelationManager = 0;
        $this->dispatch('refreshRelation');
        $this->dispatch('refreshComanda');
        ComandaLogger::make($record)->created();
        return $record;
    }

    protected function handleRecordTotaliUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        return $record;
    }
    protected function getSalvaFormAction(): FilamentActionsAction
    {
        return FilamentActionsAction::make('save')
            ->label('Salva Comanda e Calcola Totali [F1]')
            ->submit('save')
            ->keyBindings(['f1']);
    }
    protected function getFormActions(): array
    {
        return [
            $this->getSalvaFormAction(),
            //$this->getCancelFormAction(),
        ];
    }

    protected function getSalvaFormTotaliAction(): FilamentActionsAction
    {
        return FilamentActionsAction::make('saveTotali')
            ->label('Salva Totali e vai al Pagamento [F1]')
            ->submit('saveTotali')
            ->keyBindings(['f1']);
    }

    protected function getStampaTuttoAction(): FilamentActionsAction
    {
        return FilamentActionsAction::make('stampaTutto')
            ->label('Stampa Tutto [F3]')
            //->submit('saveTotali')
            ->requiresConfirmation()
            ->keyBindings(['f3'])
            ->action(function ($state): void {
                StampaScontrino::run(Comanda::find($state["id"]), 'tutto');
            });
    }

    protected function getFormTotaliActions(): array
    {
        return [
            $this->getSalvaFormTotaliAction(),
            //$this->getStampaTuttoAction()
            //$this->getCancelFormAction(),
        ];
    }

    public function saveTotali(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidateTotali');

            $data = $this->formTotali->getState(afterValidate: function () {
                $this->callHook('afterValidateTotali');

                $this->callHook('beforeSaveTotali');
            });

            $data = $this->mutateFormTotaliDataBeforeSave($data);

            $this->handleRecordTotaliUpdate($this->getRecord(), $data);

            $this->callHook('afterSaveTotali');

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->rememberData();

        if ($shouldSendSavedNotification) {
            $this->getSavedTotaliNotification()?->send();
        }

        SyncComandePostazioni::run(ModelsComanda::find($this->record["id"]));

        if ($shouldRedirect && ($redirectUrl = $this->getRedirectUrl())) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
        $this->activeRelationManager = 1;
    }
    protected function getSavedNotificationTitle(): ?string
    {
        return "Comanda Salvata: controlla il riepilogo e procedi con il pagamento!";
    }
    protected function getSavedTotaliNotificationTitle(): ?string
    {
        return "Pagamento inserito correttamente!";
    }

    protected function getSavedTotaliNotification(): ?Notification
    {
        $title = $this->getSavedTotaliNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($this->getSavedTotaliNotificationTitle());
    }



    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    protected function hasFullWidthFormTotaliActions(): bool
    {
        return true;
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    protected function getActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label("Crea Nuova Comanda [F3]")
                ->model(Comanda::class)
                ->form([
                    TextInput::make('nominativo')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('tavolo')
                        ->maxLength(255),
                    Toggle::make('asporto'),
                ])
                ->keyBindings(["f3"])
                ->action(function (array $data): void {
                    $comanda = new \App\Models\Comanda();
                    $comanda->nominativo = $data["nominativo"];
                    $comanda->tavolo = $data["tavolo"];
                    $comanda->asporto = $data["asporto"];
                    $comanda->save();
                    redirect()->route('filament.cassa.resources.comandas.comanda', ['record' => $comanda]);
                })
        ];
    }
}
