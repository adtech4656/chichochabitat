<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Save permissions
            </x-filament::button>

            <span class="text-sm" style="color: rgba(165,214,167,.7)">
                Administrators always hold every permission and are not listed here.
            </span>
        </div>
    </form>
</x-filament-panels::page>
