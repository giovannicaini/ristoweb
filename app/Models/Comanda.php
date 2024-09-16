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
class Comanda extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comande';

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function cassiere(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cassiere_id');
    }

    public function conto(): BelongsTo
    {
        return $this->belongsTo(Conto::class);
    }

    public function comande_dettagli(): HasMany
    {
        return $this->hasMany(ComandaDettaglio::class);
    }
}
