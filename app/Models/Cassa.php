<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cassa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'casse';

    public function stampante(): BelongsTo
    {
        return $this->belongsTo(Stampante::class);
    }

    public function comande(): HasMany
    {

        return $this->hasMany(Comanda::class);
    }

    ///// DA FARE CON LA SESSION
    public static function Corrente()
    {

        return session('cassa_corrente_id', null);
    }
}
