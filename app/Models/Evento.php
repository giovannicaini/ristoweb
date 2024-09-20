<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'eventi';

    public function categorie(): HasMany
    {
        return $this->hasMany(Categoria::class);
    }

    public function comande(): HasMany
    {
        return $this->hasMany(Comanda::class);
    }

    public function conti(): HasMany
    {
        return $this->hasMany(Conto::class);
    }

    public function postazioni(): HasMany
    {
        return $this->hasMany(Postazione::class);
    }

    public function prodotti(): HasMany
    {
        return $this->hasMany(Prodotto::class);
    }

    public function pagamenti(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function tipologie_pagamenti(): HasMany
    {
        return $this->hasMany(TipologiaPagamento::class);
    }

    public static function Corrente()
    {
        return self::where('attivo', true)->first()->id;
    }
}
