<?php

namespace App\Filament\Cassa\Resources\ComandaResource\Pages;

use App\Filament\Resources\ComandaResource;
use App\Models\Comanda;
use Filament\Resources\Pages\Page;

class GestioneComanda extends Page
{
    protected static string $resource = ComandaResource::class;
    protected string $view = 'filament.resources.comande.pages.gestione-comanda';

    public Comanda $record;

    public function mount(int|string $record_id): void
    {
        $this->record = Comanda::query()
            ->with([
                'pagamenti',
                'comande_dettagli.prodotto.categoria',
                'cassiere',
                'cassa',
            ])
            ->findOrFail($record_id);
    }

    protected function getHeaderHeading(): ?string
    {
        return "Gestione Comanda #{$this->record->id}";
    }

    protected function getHeaderSubheading(): ?string
    {
        $coperti = $this->record->numero_coperti();
        return "Cassiere: " . (optional($this->record->cassiere)->name ?? '—') .
            " • Cassa: " . (optional($this->record->cassa)->nome ?? '—') .
            " • Coperti: {$coperti}";
    }

    protected function getHeaderWidgets(): array
    {
        // se vuoi puoi riprendere le Cards coi totali qui, ma teniamo leggero e mettiamo i totali nel footer sticky
        return [];
    }
}