<?php
// app/Livewire/Comande/ComandaProductGrid.php

namespace App\Livewire\Comande;

use App\Models\Comanda;
use App\Models\Categoria; // o come si chiama il tuo model di categoria
use App\Models\ComandaDettaglio;
use Livewire\Component;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ComandaProductGrid extends Component
{
    public int $comandaId;

    /** @var array<int,int> prodotto_id => quantita */
    public array $quantities = [];

    public function mount(): void
    {
        $this->syncQuantities();
    }

    protected function syncQuantities(): void
    {
        $dettagli = ComandaDettaglio::query()
            ->where('comanda_id', $this->comandaId)
            ->get(['prodotto_id', 'quantita']);

        $this->quantities = $dettagli->pluck('quantita', 'prodotto_id')->map(fn ($q) => (int)$q)->toArray();
    }

    public function increment(int $prodottoId): void
    {
        DB::transaction(function () use ($prodottoId) {
            /** @var Comanda $comanda */
            $comanda = Comanda::with('comande_dettagli')->findOrFail($this->comandaId);

            $riga = $comanda->comande_dettagli()->where('prodotto_id', $prodottoId)->lockForUpdate()->first();
            if ($riga) {
                $riga->increment('quantita');
            } else {
                $comanda->comande_dettagli()->create([
                    'prodotto_id'     => $prodottoId,
                    'quantita'        => 1,
                    'sconto_unitario' => 0, // se serve
                    // altre colonne default…
                ]);
            }
        });

        $this->syncQuantities();
        $this->emitTotals();
        Notification::make()->title('Aggiunto')->success()->send();
    }

    public function decrement(int $prodottoId): void
    {
        DB::transaction(function () use ($prodottoId) {
            /** @var Comanda $comanda */
            $comanda = Comanda::with('comande_dettagli')->findOrFail($this->comandaId);

            $riga = $comanda->comande_dettagli()->where('prodotto_id', $prodottoId)->lockForUpdate()->first();
            if (!$riga) {
                return;
            }

            if ($riga->quantita > 1) {
                $riga->decrement('quantita');
            } else {
                $riga->delete();
            }
        });

        $this->syncQuantities();
        $this->emitTotals();
        Notification::make()->title('Rimosso')->color('gray')->send();
    }

    protected function emitTotals(): void
    {
        $comanda = Comanda::with(['pagamenti', 'comande_dettagli.prodotto'])->find($this->comandaId);
        $this->dispatchBrowserEvent('comanda-totali', [
            'totale' => (float) $comanda->totale_finale,
            'pagato' => (float) $comanda->totale_pagato,
        ]);
    }

    public function render()
    {
        // Carico categorie + prodotti già ordinati
        $categorie = \App\Models\Categoria::query()
            ->with(['prodotti' => function ($q) {
                $q->orderBy('ordine')->orderBy('nome');
            }])
            ->orderBy('ordine')
            ->get();

        return view('livewire.comande.comanda-product-grid', [
            'categorie' => $categorie,
        ]);
    }
}