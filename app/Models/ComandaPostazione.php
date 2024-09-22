<?php

namespace App\Models;

use App\Models\Scopes\EventoScope;
use App\Observers\AddEventoIdObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([EventoScope::class])]
#[ObservedBy([AddEventoIdObserver::class])]
class ComandaPostazione extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comande_postazioni';

    //    protected $attributes = ['printed', 'delivered'];

    protected $appends = ['printed', 'delivered'];

    public function comanda(): BelongsTo
    {
        return $this->belongsTo(Comanda::class);
    }
    /*public function comandaNS(): BelongsTo
    {
        return $this->belongsTo(Comanda::class)->withoutGlobalScopes();
    }
*/
    public function postazione(): BelongsTo
    {
        return $this->belongsTo(Postazione::class);
    }

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    public function getPrintedAttribute()
    {
        return $this->printed_at ? true : false;
    }

    public function getDeliveredAttribute()
    {
        return $this->delivered_at ? true : false;
    }

    public function getAttesaAttribute()
    {
        if ($this->printed_at) {
            return $this->delivered_at ? Carbon::createFromDate($this->printed_at)->diffInMinutes($this->delivered_at) : Carbon::createFromDate($this->printed_at)->diffInMinutes(now());
        } else
            return null;
    }
}
