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
class Conto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'conti';

    public function comande(): HasMany
    {
        return $this->hasMany(Comanda::class);
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }
}
