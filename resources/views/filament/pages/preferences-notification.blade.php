<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end gap-3">
            <x-filament::button type="submit" color="primary" icon="heroicon-o-check">
                💾 Enregistrer mes préférences
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>