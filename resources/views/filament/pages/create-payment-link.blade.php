<x-filament::page>
    <form wire:submit.prevent="createPaymentLink" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Create Payment Link
        </x-filament::button>
    </form>
</x-filament::page>