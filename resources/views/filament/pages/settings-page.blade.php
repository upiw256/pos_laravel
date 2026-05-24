<x-filament-panels::page>
    <x-filament::form wire:submit="save">
        {{ $this->form }}

        <div class="mt-4 flex gap-4">
            <x-filament::button type="submit">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </x-filament::form>
</x-filament-panels::page>
