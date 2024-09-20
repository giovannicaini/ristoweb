<?php

namespace App\Models;

use App\Models\Scopes\EventoScope;
use App\Observers\AddEventoIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([EventoScope::class])]
#[ObservedBy([AddEventoIdObserver::class])]

class TipologiaPagamento extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'tipologie_pagamento';


    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function pagamenti(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }
}
