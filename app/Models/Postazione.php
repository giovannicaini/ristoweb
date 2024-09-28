<?php

namespace App\Models;

use App\Models\Scopes\EventoScope;
use App\Observers\AddEventoIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([EventoScope::class])]
#[ObservedBy([AddEventoIdObserver::class])]
class Postazione extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'postazioni';

    public function categorie(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }

    public function categorieNS(): HasMany
    {
        return $this->hasMany(Categoria::class)->withoutGlobalScopes();
    }


    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function stampante(): BelongsTo
    {
        return $this->belongsTo(Stampante::class);
    }

    public function comande(): BelongsToMany
    {
        return $this->belongsToMany(Comanda::class, ComandaPostazione::class);
    }
}
