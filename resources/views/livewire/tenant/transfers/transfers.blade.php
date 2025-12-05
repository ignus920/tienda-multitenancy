<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transferencias') }}
        </h2>
    </x-slot>
    
    <!-- Livewire Component -->
    <livewire:tenant.transfers.transfer-form />
</x-app-layout>