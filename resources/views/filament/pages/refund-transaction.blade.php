<x-filament::page>
    <form wire:submit.prevent="refundTransaction" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            Refund payment
        </x-filament::button>
    </form>
</x-filament::page>