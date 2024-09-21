<?php

namespace App\Observers;

use App\Filament\Cassa\Resources\ComandaResource\Pages\Comanda;
use App\Models\Cassa;
use App\Models\Comanda as ModelsComanda;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Model;

class ComandeObserver
{

    public function creating(Model $model): void
    {
        $last_comanda = ModelsComanda::orderBy('n_ordine', 'DESC')->get()->first();
        $last_comanda ? $last_comanda->n_ordine + 1 : 1;
        $model->n_ordine = $model->n_ordine ?? ($last_comanda ? $last_comanda->n_ordine + 1 : 1);
        $model->cassiere_id = auth()->user()->id;
        $model->cassa_id = Cassa::Corrente();
    }

    private function syncPostazioniDaComanda(Comanda $comanda) {}
}
