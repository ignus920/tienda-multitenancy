@props(['active' => 'dashboard'])

@php
    $tabs = [
        'dashboard' => ['label' => 'Inicio',       'route' => 'tenant.client.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        'orders'    => ['label' => 'Mis Pedidos',   'route' => 'tenant.client.orders',    'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
        'invoices'  => ['label' => 'Mis Facturas',  'route' => 'tenant.client.invoices',  'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        'catalog'   => ['label' => 'Catálogo',      'route' => 'tenant.client.portal',    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
    ];
@endphp

<div class="sticky top-16 z-30 -mx-4 mb-4 border-b border-gray-200/80 bg-white/95 px-4 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95 sm:-mx-6 sm:px-6">
    <nav class="mx-auto flex max-w-6xl gap-1 overflow-x-auto" aria-label="Panel del cliente">
        @foreach ($tabs as $key => $tab)
            @php $is = $key === $active; @endphp
            <a href="{{ route($tab['route']) }}" wire:navigate
               @class([
                   'group relative flex shrink-0 items-center gap-2 px-3 py-3.5 text-sm font-semibold transition-colors',
                   'text-indigo-600 dark:text-indigo-400' => $is,
                   'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100' => !$is,
               ])>
                <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="{{ $is ? 2.2 : 1.8 }}">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}" />
                </svg>
                <span>{{ $tab['label'] }}</span>
                <span @class([
                    'absolute inset-x-2 -bottom-px h-0.5 rounded-full transition-all',
                    'bg-indigo-600 dark:bg-indigo-400' => $is,
                    'bg-transparent group-hover:bg-gray-300 dark:group-hover:bg-gray-700' => !$is,
                ])></span>
            </a>
        @endforeach
    </nav>
</div>
