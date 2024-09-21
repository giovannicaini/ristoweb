<?php

namespace App\Models\Scopes;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EventoScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $column_name = $model->getTable() . '.evento_id';
        $builder->where($column_name, Evento::Corrente());
    }
}
