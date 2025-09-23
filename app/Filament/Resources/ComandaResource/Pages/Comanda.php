<?php

namespace App\Filament\Resources\ComandaResource\Pages;

use App\Filament\Resources\ComandaResource;
use App\Models\ComandaDettaglio;
use App\Models\Prodotto;
use Filament\Actions;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Resources\Pages\EditRecord;
use Filament\Tables\Actions\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;

class Comanda extends EditRecord
{
    protected static string $resource = ComandaResource::class;
    protected static string $layout = 'no-menu';
    public string $view = 'comanda';
    protected $listeners = ['refreshComanda' => '$refresh'];

    #[On('refreshComanda')]
    public function refresh(): void
    {
        $this->fillForm();
    }
    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form2' => $this->form(static::getResource()::formComanda(
                $this->makeForm()
                    ->operation('edit')
                    ->model($this->getRecord())
                    ->statePath($this->getFormStatePath())
                    ->columns($this->hasInlineLabels() ? 1 : 2)
                    ->inlineLabel($this->hasInlineLabels()),
            )),
            'formTotali' => $this->form(static::getResource()::form(
                $this->makeForm()
                    ->operation('edit')
                    ->model($this->getRecord())
                    ->statePath($this->getFormStatePath())
                    ->columns($this->hasInlineLabels() ? 1 : 2)
                    ->inlineLabel($this->hasInlineLabels()),
            )),
        ];
    }

    protected function fillForm(): void
    {
        /** @internal Read the DocBlock above the following method. */
        $qta_prodotti = [];
        foreach (Prodotto::get() as $prodotto) {
            $test = ComandaDettaglio::where('comanda_id', $this->getRecord()->id)->where('prodotto_id', $prodotto->id)->first();
            if ($test) {
                $qta_prodotti['prodotto_' . $prodotto->id] = $test->quantita;
                if ($test->note)
                    $qta_prodotti['note_' . $prodotto->id] = $test->note;
            }
        }
        $this->fillFormWithDataAndCallHooks($this->getRecord(), $qta_prodotti);
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $dettagli = [];
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
        $data = [];
        $data['comande_dettagli'] = $dettagli;
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
        $this->dispatch('refreshRelation');
        return $record;
    }
}
