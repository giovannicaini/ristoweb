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
class Prodotto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prodotti';

    public function comande_dettagli(): HasMany
    {
        return $this->hasMany(ComandaDettaglio::class);
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function (Prodotto $model) {
            if (!$model->evento_id)
                $model->evento_id = Evento::where('attivo', true)->first()->id;
        });
    }
}
