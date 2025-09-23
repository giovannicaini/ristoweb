<x-filament-panels::page>
    {{-- Barra sticky con stato pagamenti (semplice) --}}
    @php
        $tot = (float) $this->record->totale_finale;
        $pag = (float) $this->record->totale_pagato;
        $perc = $tot > 0 ? round(($pag / $tot) * 100) : 0;
    @endphp

    <div class="sticky top-0 z-10 bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60 border-b">
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Totale: <strong>€ {{ number_format($tot, 2, ',', '.') }}</strong> •
                Pagato: <strong>€ {{ number_format($pag, 2, ',', '.') }}</strong>
            </div>
            <div class="w-52">
                <div class="h-2 bg-gray-100 rounded overflow-hidden">
                    <div class="h-2 bg-primary-600" style="width: {{ $perc }}%"></div>
                </div>
                <div class="mt-1 text-[11px] text-right text-gray-500">{{ $perc }}%</div>
            </div>
        </div>
    </div>

    {{-- Griglia prodotti raggruppata per categoria --}}
    @livewire(\App\Livewire\Comande\ComandaProductGrid::class, ['comandaId' => $this->record->id], key('grid-'.$this->record->id))

    {{-- Footer sticky coi totali aggiornati in tempo reale --}}
    <div x-data="{ tot: {{ $tot }}, pag: {{ $pag }} }"
         x-on:comanda-totali.window="tot = $event.detail.totale; pag = $event.detail.pagato"
         class="sticky bottom-0 z-10 border-t bg-white/90 backdrop-blur">
        @php $da = max(0, $tot - $pag); @endphp
        <div class="px-4 py-3 flex items-center justify-end gap-6 text-sm">
            <div>Totale: <strong x-text="`€ ${tot.toFixed(2).replace('.', ',')}`">€ {{ number_format($tot, 2, ',', '.') }}</strong></div>
            <div>Pagato: <strong x-text="`€ ${pag.toFixed(2).replace('.', ',')}`">€ {{ number_format($pag, 2, ',', '.') }}</strong></div>
            <div>Da pagare:
                <strong
                    :class="(tot - pag) > 0 ? 'text-amber-600' : 'text-emerald-600'"
                    x-text="`€ ${(Math.max(0, tot - pag)).toFixed(2).replace('.', ',')}`">
                    € {{ number_format($da, 2, ',', '.') }}
                </strong>
            </div>
        </div>
    </div>
</x-filament-panels::page>