<div
    x-data="comandaGrid({
        selected: @js($quantities),
        initialCat: null,
    })"
    class="space-y-6"
>
    <!-- Toolbar: ricerca + filtri -->
    <div class="flex flex-wrap items-center gap-3 px-2">
        <div class="relative">
            <input
                x-model="q"
                type="search"
                placeholder="Cerca prodotto…"
                class="fi-input block w-64 rounded-xl border-gray-300 pl-9"
            />
            <div class="pointer-events-none absolute inset-y-0 left-2 flex items-center">
                <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-4 w-4 text-gray-400" />
            </div>
        </div>

        <div class="hidden md:flex items-center gap-2 overflow-x-auto">
            <button
                @click="activeCat = null"
                :class="buttonPillClass(activeCat === null)"
                class="fi-btn px-3 py-1.5 text-sm"
            >Tutte</button>

            @foreach ($categorie as $cat)
                <button
                    @click="activeCat = {{ $cat->id }}"
                    :class="buttonPillClass(activeCat === {{ $cat->id }})"
                    class="fi-btn px-3 py-1.5 text-sm"
                >{{ $cat->nome }}</button>
            @endforeach
        </div>

        <div class="ml-auto flex items-center gap-3">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" x-model="onlySelected" class="rounded border-gray-300">
                <span>Solo selezionati</span>
            </label>

            <div class="hidden sm:flex items-center gap-1 text-xs text-gray-500">
                <span class="h-2 w-2 rounded-full bg-primary-600"></span>
                <span>Selezionati: <span x-text="selectedCount()"></span></span>
            </div>
        </div>
    </div>

    <!-- Categorie + griglie auto-fit -->
    @foreach ($categorie as $cat)
        <section
            x-show="!activeCat || activeCat === {{ $cat->id }}"
            x-cloak
            class="space-y-3"
        >
            <!-- Header categoria sticky su scroll -->
            <div class="sticky top-[64px] z-10 bg-white/85 backdrop-blur border-l-4 border-primary-500 pl-3 py-1.5">
                <div class="flex items-center justify-between">
                    <h3 class="text-[11px] font-semibold tracking-wide uppercase text-gray-600">
                        {{ $cat->nome }}
                    </h3>
                    <div class="text-[11px] text-gray-500">
                        <span x-show="catTotal({{ $cat->id }}) > 0">
                            Subtotale cat: € <span x-text="formatMoney(catTotal({{ $cat->id }}))"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Grid: auto-fit minmax -->
            <div
                class="grid gap-3 px-1"
                style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));"
            >
                @foreach ($cat->prodotti as $p)
                    @php $qta = $quantities[$p->id] ?? 0; @endphp

                    <article
                        x-show="filter({ id: {{ $p->id }}, name: @js($p->nome) })"
                        x-cloak
                        class="group rounded-2xl border bg-white/90 shadow-sm ring-1 ring-gray-100 transition
                               hover:shadow-md hover:-translate-y-0.5"
                    >
                        <!-- Top: nome + prezzo -->
                        <div class="p-3 pb-2">
                            <div class="line-clamp-2 text-sm font-semibold leading-snug">
                                {{ $p->nome }}
                            </div>
                            <div class="mt-1 text-xs text-gray-500">
                                € {{ number_format($p->prezzo, 2, ',', '.') }}
                            </div>
                        </div>

                        <!-- Bottom: qty + controls + totale riga -->
                        <div class="flex items-center justify-between gap-2 px-3 pb-3">
                            <div class="inline-flex items-center gap-1.5">
                                <button
                                    class="fi-btn fi-btn-size-sm fi-btn-color-gray rounded-full"
                                    :disabled="qty({{ $p->id }}) === 0"
                                    @click="$wire.decrement({{ $p->id }}); dec({{ $p->id }})"
                                >
                                    <x-filament::icon icon="heroicon-m-minus-small" class="h-4 w-4" />
                                </button>

                                <span
                                    class="inline-flex h-7 w-9 items-center justify-center rounded-full border bg-white text-sm font-semibold
                                           transition group-hover:border-primary-200"
                                    x-text="qty({{ $p->id }})"
                                >{{ $qta }}</span>

                                <button
                                    class="fi-btn fi-btn-size-sm fi-btn-color-primary rounded-full"
                                    @click="$wire.increment({{ $p->id }}); inc({{ $p->id }}, {{ (float) $p->prezzo }}, {{ $cat->id }})"
                                >
                                    <x-filament::icon icon="heroicon-m-plus-small" class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="text-[13px] font-medium tabular-nums">
                                € <span
                                    x-text="formatMoney(qty({{ $p->id }}) * {{ (float) $p->prezzo }})"
                                >{{ number_format($qta * $p->prezzo, 2, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Barra progresso mini (quantità) -->
                        <div class="mx-3 mb-3 h-1.5 rounded bg-gray-100 overflow-hidden">
                            <div
                                class="h-1.5 bg-primary-500 transition-all"
                                :style="`width: ${Math.min(100, qty({{ $p->id }})*10)}%`"
                            ></div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endforeach

    @if ($categorie->isEmpty())
        <div class="px-2 text-sm text-gray-500">Nessun prodotto disponibile.</div>
    @endif

    <!-- Floating footer: riepilogo rapido -->
    <div
        class="pointer-events-none fixed inset-x-0 bottom-3 mx-auto w-full max-w-5xl px-3 sm:px-6"
        x-show="selectedCount() > 0"
        x-cloak
    >
        <div class="pointer-events-auto rounded-2xl border bg-white/95 shadow-xl backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-primary-600 text-white text-xs font-semibold"
                          x-text="selectedCount()"></span>
                    <span>prodotti selezionati</span>
                </div>
                <div class="flex items-center gap-4">
                    <div>Subtotale selezione:
                        <strong>€ <span x-text="formatMoney(selectedSubtotal())"></span></strong>
                    </div>
                    <x-filament::button
                        color="primary"
                        size="sm"
                        icon="heroicon-m-arrow-path"
                        @click="$dispatch('comanda-refresh')"
                    >Aggiorna totali</x-filament::button>
                </div>
            </div>
        </div>
    </div>
    <!-- Alpine component -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('comandaGrid', (opts) => ({
            q: '',
            onlySelected: false,
            activeCat: opts.initialCat,
            selected: Object.assign({}, opts.selected ?? {}), // { prodottoId: qty }
            perCat: {}, // { catId: subtotal }
            // helpers
            buttonPillClass(active) {
                return active
                    ? 'fi-btn-color-primary'
                    : 'fi-btn-color-gray';
            },
            formatMoney(v) {
                return (v || 0).toFixed(2).replace('.', ',');
            },
            qty(id) { return Number(this.selected[id] || 0); },
            inc(id, price, catId) {
                this.selected[id] = this.qty(id) + 1;
                this.bumpCat(catId, +price);
            },
            dec(id) {
                this.selected[id] = Math.max(0, this.qty(id) - 1);
            },
            selectedCount() {
                return Object.values(this.selected).reduce((a, b) => a + (Number(b) > 0 ? 1 : 0), 0);
            },
            selectedSubtotal() {
                // questo è solo un quick sum lato client (indicativo)
                return 0; // opzionale: puoi calcolarlo se passi i prezzi in un map
            },
            bumpCat(catId, delta) {
                if (!catId) return;
                this.perCat[catId] = (this.perCat[catId] || 0) + (delta || 0);
            },
            catTotal(catId) { return this.perCat[catId] || 0; },
            filter(prod) {
                const matchesText = this.q.trim().length
                    ? prod.name.toLowerCase().includes(this.q.toLowerCase())
                    : true;
                const matchesSelected = this.onlySelected ? (this.qty(prod.id) > 0) : true;
                return matchesText && matchesSelected;
            },
        }))
    })
</script>

<style>
    /* piccoli ritocchi */
    .fi-btn.fi-btn-color-gray { @apply border border-gray-200 bg-white hover:bg-gray-50; }
    .fi-btn.fi-btn-color-primary { @apply bg-primary-600 text-white hover:bg-primary-700; }
</style>

</div>