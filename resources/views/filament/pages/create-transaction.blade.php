<x-filament::page>
    <form wire:submit.prevent="createTransaction" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Create transaction
        </x-filament::button>
    </form>
</x-filament::page>