<?php

namespace App\Observers;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Model;

class AddEventoIdObserver
{

    public function creating(Model $model): void
    {
        $model->evento_id = $model->evento_id ?? Evento::Corrente();
    }
}
