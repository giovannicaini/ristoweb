<?php

namespace App\Models;

use App\Models\Scopes\EventoScope;
use App\Observers\AddEventoIdObserver;
use App\Observers\ComandeObserver;
use Attribute;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute as CastsAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([EventoScope::class])]
#[ObservedBy([AddEventoIdObserver::class])]
#[ObservedBy([ComandeObserver::class])]
class Comanda extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comande';

    //Add extra attribute
    //protected $attributes = ['totale_prodotti_senza_sconto', 'totale_prodotti_con_sconto', 'totale_sconto_prodotti', 'totale_finale', 'totale_da_pagare', 'totale_pagato'];

    //Make it available in the json response
    protected $appends = ['totale_prodotti_senza_sconto', 'totale_prodotti_con_sconto', 'totale_sconto_prodotti', 'totale_finale', 'totale_da_pagare', 'totale_pagato'];

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

    public function cassa(): BelongsTo
    {
        return $this->belongsTo(Cassa::class);
    }

    public function comande_dettagli(): HasMany
    {
        return $this->hasMany(ComandaDettaglio::class);
    }

    public function comande_postazioni(): HasMany
    {
        return $this->hasMany(ComandaPostazione::class);
    }

    public function postazioni(): BelongsToMany
    {
        return $this->belongsToMany(Postazione::class, ComandaPostazione::class);
    }

    public function pagamenti(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    public function numero_coperti()
    {
        $coperto = ComandaDettaglio::where('comanda_id',$this->id)->where('prodotto_id', Prodotto::where('coperto', true)->first()->id)->first();
        return $coperto ? $coperto->quantita : 0;
    }

    public function getTotaleProdottiSenzaScontoAttribute()
    {
        $totale = 0;
        foreach ($this->comande_dettagli as $dettaglio)
            $totale += $dettaglio->prodotto->prezzo * $dettaglio->quantita;
        return $totale;
    }

    public function getTotaleScontoProdottiAttribute()
    {

        $sconto = 0;
        foreach ($this->comande_dettagli as $dettaglio)
            $sconto += $dettaglio->sconto_unitario * $dettaglio->quantita;
        return $sconto;
    }

    public function getTotaleProdottiConScontoAttribute()
    {

        return $this->totale_prodotti_senza_sconto - $this->totale_sconto_prodotti;
    }

    public function getTotaleFinaleAttribute()
    {
        return $this->totale_prodotti_con_sconto - $this->su_conto;
    }

    public function getTotalePagatoAttribute()
    {
        return $this->pagamenti->sum('importo');
    }

    public function getTotaleDaPagareAttribute()
    {
        return $this->totale_finale - $this->totale_pagato;
    }




    public function getEventoId()
    {
        $this->evento_id ?? Evento::Corrente();
    }
}
