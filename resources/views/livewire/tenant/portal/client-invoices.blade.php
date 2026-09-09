<div class="min-h-screen bg-gray-50 px-4 pb-16 pt-4 dark:bg-gray-950 sm:px-6">
    <x-portal-nav active="invoices" />

    <div class="mx-auto max-w-5xl">
        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">Mis Facturas</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Descarga el PDF oficial de tus facturas electrónicas.</p>

        {{-- Filtros --}}
        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar por número de factura…"
                       class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-3 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-800 dark:bg-gray-900 dark:text-white">
            </div>
            <div class="flex gap-2">
                @foreach (['' => 'Todas', 'pending' => 'Pendientes', 'paid' => 'Pagadas'] as $val => $lbl)
                    <button type="button" wire:click="$set('paymentFilter', '{{ $val }}')"
                            @class([
                                'shrink-0 rounded-full px-3.5 py-2 text-xs font-bold transition',
                                'bg-indigo-600 text-white shadow-sm shadow-indigo-600/20' => $paymentFilter === $val,
                                'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:ring-gray-800' => $paymentFilter !== $val,
                            ])>
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Tabla / lista --}}
        <div class="mt-5 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900" wire:loading.class="opacity-50">
            {{-- header (desktop) --}}
            <div class="hidden grid-cols-12 gap-3 border-b border-gray-100 px-5 py-3 text-[11px] font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-500 sm:grid">
                <span class="col-span-3">Factura</span>
                <span class="col-span-2">Fecha</span>
                <span class="col-span-2">Pedido</span>
                <span class="col-span-2 text-right">Total</span>
                <span class="col-span-1 text-center">Estado</span>
                <span class="col-span-2 text-right">PDF</span>
            </div>

            @forelse ($rows as $inv)
                <div wire:key="inv-{{ $inv['id'] }}"
                     class="grid grid-cols-2 items-center gap-3 border-b border-gray-100 px-5 py-4 last:border-0 dark:border-gray-800 sm:grid-cols-12">
                    <div class="col-span-2 sm:col-span-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-500 dark:bg-indigo-900/30 dark:text-indigo-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <span class="font-extrabold text-gray-900 dark:text-white">{{ $inv['number'] }}</span>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 sm:col-span-2">
                        {{ \Carbon\Carbon::parse($inv['date'])->translatedFormat('d M Y') }}
                    </div>
                    <div class="text-xs sm:col-span-2">
                        @if ($inv['order_id'])
                            <a href="{{ route('tenant.client.orders.show', $inv['order_id']) }}" wire:navigate
                               class="font-bold text-indigo-600 hover:underline dark:text-indigo-400">#{{ $inv['order_no'] }}</a>
                        @else
                            <span class="text-gray-300 dark:text-gray-600">—</span>
                        @endif
                    </div>
                    <div class="text-right font-extrabold text-gray-900 dark:text-white sm:col-span-2">
                        ${{ number_format($inv['total'], 0, ',', '.') }}
                    </div>
                    <div class="col-span-2 sm:col-span-1 sm:text-center">
                        <span class="inline-block rounded-full px-2 py-0.5 text-[11px] font-bold {{ $inv['badge']['color'] }}">{{ $inv['badge']['label'] }}</span>
                    </div>
                    <div class="col-span-2 text-right sm:col-span-2">
                        @if ($inv['downloadable'])
                            <a href="{{ route('tenant.client.invoice.pdf', $inv['id']) }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-indigo-700">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Descargar
                            </a>
                        @else
                            <span class="text-[11px] font-semibold text-gray-400 dark:text-gray-500">Sin emitir</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-16 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="mt-3 font-semibold text-gray-700 dark:text-gray-200">
                        @if ($search !== '' || $paymentFilter !== '') No hay facturas con esos filtros @else Todavía no tienes facturas @endif
                    </p>
                    @if ($search !== '' || $paymentFilter !== '')
                        <button wire:click="clearFilters" class="mt-3 text-sm font-bold text-indigo-600 hover:underline dark:text-indigo-400">Quitar filtros</button>
                    @endif
                </div>
            @endforelse
        </div>

        @if ($invoices->hasPages())
            <div class="mt-6">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
