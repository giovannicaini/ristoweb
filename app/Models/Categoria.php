<?php

namespace App\Models;

use App\Models\Scopes\EventoScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([EventoScope::class])]
#[ObservedBy([AddEventoIdObserver::class])]
class Categoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categorie';

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function postazione(): BelongsTo
    {
        return $this->belongsTo(Postazione::class);
    }

    public function prodotti(): HasMany
    {
        return $this->hasMany(Prodotto::class);
    }
}
