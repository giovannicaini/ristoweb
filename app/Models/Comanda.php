<?php

namespace App\Models;

use App\Models\Scopes\EventoScope;
use App\Observers\AddEventoIdObserver;
use Attribute;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute as CastsAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([EventoScope::class])]
#[ObservedBy([AddEventoIdObserver::class])]

class Comanda extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comande';

    //Add extra attribute
    protected $attributes = ['totale_prodotti_senza_sconto', 'totale_prodotti_con_sconto', 'totale_sconto_prodotti'];

    //Make it available in the json response
    protected $appends = ['totale_prodotti_senza_sconto', 'totale_prodotti_con_sconto', 'totale_sconto_prodotti'];

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

    public function numero_coperti()
    {
        return ComandaDettaglio::where('prodotto_id', Prodotto::where('coperto', true)->first())->first()->quantita;
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

    public function getEventoId()
    {
        $this->evento_id ?? Evento::Corrente();
    }
}
