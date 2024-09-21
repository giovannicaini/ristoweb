<?php

namespace App\Observers;

use App\Filament\Cassa\Resources\ComandaResource\Pages\Comanda;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Model;

class ComandePostazioniObserver
{

    public function creating(Model $model): void
    {
        $model->evento_id = $model->evento_id ?? Evento::Corrente();
    }

    private function syncPostazioniDaComanda(Comanda $comanda) {}
}
