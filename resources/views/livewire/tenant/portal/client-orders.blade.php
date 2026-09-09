<div class="min-h-screen bg-gray-50 px-4 pb-16 pt-4 dark:bg-gray-950 sm:px-6">
    <x-portal-nav active="orders" />

    <div class="mx-auto max-w-6xl">
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">Mis Pedidos</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Seguimiento y detalle de todos los pedidos de tu empresa.</p>

        {{-- Filtros --}}
        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar por número de pedido…"
                       class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-3 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
            </div>
            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach (['' => 'Todos', 'in_progress' => 'En curso', 'delivered' => 'Entregados', 'cancelled' => 'Anulados'] as $val => $lbl)
                    <button type="button" wire:click="$set('statusFilter', '{{ $val }}')"
                            @class([
                                'shrink-0 rounded-full px-3.5 py-2 text-xs font-bold transition',
                                'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' => $statusFilter === $val,
                                'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:ring-gray-800' => $statusFilter !== $val,
                            ])>
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Lista --}}
        <div class="mt-5 space-y-3" wire:loading.class="opacity-50">
            @forelse ($rows as $o)
                <a href="{{ route('tenant.client.orders.show', $o['id']) }}" wire:navigate wire:key="order-{{ $o['id'] }}"
                   class="block rounded-2xl border border-gray-200 bg-white p-4 transition hover:border-indigo-200 hover:shadow-md hover:shadow-gray-200/60 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-indigo-800 dark:hover:shadow-black/40 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-500 dark:from-indigo-900/40 dark:to-indigo-900/10 dark:text-indigo-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white">Pedido #{{ $o['consecutive'] }}</h3>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $o['badge']['color'] }}">{{ $o['badge']['label'] }}</span>
                                </div>
                                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                    {{ \Carbon\Carbon::parse($o['date'])->translatedFormat('d \d\e F Y') }}
                                    · {{ $o['items'] }} {{ \Illuminate\Support\Str::plural('producto', $o['items']) }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-extrabold text-gray-900 dark:text-white">${{ number_format($o['total'], 0, ',', '.') }}</p>
                            @if ($o['invoice_no'])
                                <p class="text-[11px] font-semibold text-gray-400 dark:text-gray-500">Factura {{ $o['invoice_no'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                        @if ($o['delivery'])
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Entrega: <span class="font-semibold text-gray-700 dark:text-gray-200">{{ \Carbon\Carbon::parse($o['delivery'])->translatedFormat('d M Y') }}</span>
                            </span>
                        @endif
                        @if ($o['address'])
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span class="truncate">{{ $o['address'] }}</span>
                            </span>
                        @endif
                        <span class="ml-auto inline-flex items-center gap-1 font-bold text-indigo-600 dark:text-indigo-400">
                            Ver seguimiento
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white py-16 text-center dark:border-gray-700 dark:bg-gray-900">
                    <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="mt-3 font-semibold text-gray-700 dark:text-gray-200">
                        @if ($search !== '' || $statusFilter !== '') No hay pedidos con esos filtros @else Todavía no tienes pedidos @endif
                    </p>
                    @if ($search !== '' || $statusFilter !== '')
                        <button wire:click="clearFilters" class="mt-3 text-sm font-bold text-indigo-600 hover:underline dark:text-indigo-400">Quitar filtros</button>
                    @endif
                </div>
            @endforelse
        </div>

        @if ($orders->hasPages())
            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
