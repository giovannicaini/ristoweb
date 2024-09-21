<x-filament-widgets::widget>
    <x-filament::section>
    Evento Corrente: <b>@if ($this->getEventoCorrente()) {{$this->getEventoCorrente()->nome.' - '.Date("d/m/Y",strtotime($this->getEventoCorrente()->data))}} @else Nessun evento selezionato @endif</b><br/>
    Cassa Corrente: <b>@if ($this->getCassaCorrente()) {{$this->getCassaCorrente()->nome}} @else Nessuna cassa selezionata @endif</b><br/><br/>
    
    {{ $this->testAction }}
        <x-filament-actions::modals />
    </x-filament::section>
</x-filament-widgets::widget>