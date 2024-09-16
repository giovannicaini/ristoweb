<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComandaDettaglio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comande_dettagli';

    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class);
    }

    public function prodotto(): BelongsTo
    {
        return $this->belongsTo(Prodotto::class);
    }
}
