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
class Postazione extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'postazioni';

    public function categorie(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function stampante(): BelongsTo
    {
        return $this->belongsTo(Stampante::class);
    }
}
