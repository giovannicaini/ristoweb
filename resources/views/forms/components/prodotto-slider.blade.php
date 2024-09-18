<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }} }">
        
        <x-filament::input.wrapper>
            <x-filament::button wire:click="prova">
            -
        </x-filament::button>
            <x-filament::input
                type="text"
                wire:model="name"
            />
        </x-filament::input.wrapper>
    </div>
</x-dynamic-component>
