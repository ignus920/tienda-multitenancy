@props(['position' => 'bottom-right', 'size' => 'md'])

@php
    $positions = [
        'bottom-right' => 'bottom-4 right-4',
        'bottom-left' => 'bottom-4 left-4',
        'top-right' => 'top-4 right-4',
        'top-left' => 'top-4 left-4',
    ];
    
    $sizes = [
        'sm' => 'h-10 w-10',
        'md' => 'h-12 w-12',
        'lg' => 'h-14 w-14',
    ];
    
    $iconSizes = [
        'sm' => 'h-5 w-5',
        'md' => 'h-6 w-6',
        'lg' => 'h-7 w-7',
    ];
    
    $positionClass = $positions[$position] ?? $positions['bottom-right'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $iconSize = $iconSizes[$size] ?? $iconSizes['md'];
@endphp

<button
    type="button"
    onclick="Livewire.dispatch('openWarehouseSelector')"
    {{ $attributes->merge(['class' => "fixed {$positionClass} {$sizeClass} flex items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 hover:scale-110 z-40"]) }}
    title="Cambiar Sucursal"
>
    <svg class="{{ $iconSize }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-1.25 0V3.75a.75.75 0 00-.75-.75H14.25a.75.75 0 00-.75.75V4.5" />
    </svg>
</button>
