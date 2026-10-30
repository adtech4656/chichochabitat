<x-filament-panels::page>
    <div class="mb-4 flex items-center gap-2 text-sm" style="color: rgba(232,245,233,.7)">
        <x-filament::icon icon="heroicon-o-language" class="h-4 w-4" />
        <span>
            Editing
            <strong style="color: #66BB6A">{{ $locale === 'fr' ? 'Français' : 'English' }}</strong>
            — switch language with the buttons at the top right. Empty fields fall back to English on the public site.
        </span>
    </div>

    <form wire:submit="save">
        {{ $this->form }}

        @if (\App\Filament\Pages\SiteConfiguration::canEditSettings())
            <div class="mt-6">
                <x-filament::button type="submit" icon="heroicon-o-check">
                    Save settings
                </x-filament::button>
            </div>
        @else
            <p class="mt-6 text-sm" style="color: rgba(255,167,38,.9)">
                You have read-only access. Ask an administrator for the “Edit site content” permission.
            </p>
        @endif
    </form>
</x-filament-panels::page>
