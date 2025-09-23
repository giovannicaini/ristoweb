
        <form
            id="form"
            wire:submit="save"
        >
            {{ $this->form }}

            <x-filament::actions
                :actions="$this->getFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
    </form>
  
    <form
        id="form-totali"
        wire:submit="saveTotali"
    >
        {{ $this->formTotali }}

        <x-filament::actions
            :actions="$this->getFormTotaliActions()"
            :full-width="$this->hasFullWidthFormTotaliActions()"
        />
    </form>
    @php
        $relationManagers = $this->getRelationManagers();
        $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
    @endphp

    @if ((! $hasCombinedRelationManagerTabsWithContent) || (! count($relationManagers)))
        {{ $form() }}
    @endif

        @if ($this->activeRelationManager === '0')
            {{ $formTotali() }}
        @endif