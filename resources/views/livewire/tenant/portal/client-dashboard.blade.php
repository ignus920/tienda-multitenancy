<div class="min-h-screen bg-gray-50 px-4 pb-16 pt-4 dark:bg-gray-950 sm:px-6">
    <x-portal-nav active="dashboard" />

    <div class="mx-auto max-w-6xl">

        {{-- Saludo --}}
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400 dark:text-gray-500">Hola,</p>
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                    {{ $companyName }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Este es el resumen de tu cuenta.
                </p>
            </div>
            <a href="{{ route('tenant.client.portal') }}" wire:navigate
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-700 active:scale-[.98]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Hacer un pedido
            </a>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            {{-- Pedidos en curso --}}
            <a href="{{ route('tenant.client.orders') }}" wire:navigate
               class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gray-200/60 dark:border-gray-800 dark:bg-gray-900 dark:hover:shadow-black/40">
                <div class="flex items-center gap-2 text-cyan-600 dark:text-cyan-400">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-50 dark:bg-cyan-900/30">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM20 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/></svg>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wide">En curso</span>
                </div>
                <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $ordersInProgress }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">pedidos activos</p>
            </a>

            {{-- Próxima entrega --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-900/30">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wide">Próxima entrega</span>
                </div>
                @if ($nextDelivery && $nextDelivery->deliveryDate)
                    <p class="mt-3 text-2xl font-extrabold text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($nextDelivery->deliveryDate)->translatedFormat('d M') }}
                    </p>
                    <a href="{{ route('tenant.client.orders.show', $nextDelivery->id) }}" wire:navigate
                       class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">
                        Pedido #{{ $nextDelivery->consecutive }}
                    </a>
                @else
                    <p class="mt-3 text-2xl font-extrabold text-gray-300 dark:text-gray-700">—</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">sin entregas programadas</p>
                @endif
            </div>

            {{-- Facturas por pagar --}}
            <a href="{{ route('tenant.client.invoices') }}" wire:navigate
               class="group rounded-2xl border border-gray-200 bg-white p-4 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gray-200/60 dark:border-gray-800 dark:bg-gray-900 dark:hover:shadow-black/40 {{ $unpaidCount > 0 ? 'ring-1 ring-amber-300 dark:ring-amber-700' : '' }}">
                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h6m-6 4h4m5 6H5a2 2 0 01-2-2V5a2 2 0 012-2h9l6 6v10a2 2 0 01-2 2z"/></svg>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wide">Por pagar</span>
                </div>
                <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $unpaidCount }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    @if ($unpaidTotal > 0) ${{ number_format($unpaidTotal, 0, ',', '.') }} @else facturas @endif
                </p>
            </a>

            {{-- Total histórico --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wide">Historial</span>
                </div>
                <p class="mt-3 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $totalOrders }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">pedidos en total</p>
            </div>
        </div>

        {{-- Pedidos recientes --}}
        <div class="mt-8">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Últimos pedidos</h2>
                <a href="{{ route('tenant.client.orders') }}" wire:navigate
                   class="text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Ver todos</a>
            </div>

            @if ($recentOrders->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white py-14 text-center dark:border-gray-700 dark:bg-gray-900">
                    <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <p class="mt-3 font-semibold text-gray-700 dark:text-gray-200">Todavía no tienes pedidos</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500">Cuando hagas tu primer pedido, aparecerá acá.</p>
                    <a href="{{ route('tenant.client.portal') }}" wire:navigate
                       class="mt-4 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700">
                        Explorar catálogo
                    </a>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    @foreach ($recentOrders as $o)
                        <a href="{{ route('tenant.client.orders.show', $o['id']) }}" wire:navigate wire:key="recent-{{ $o['id'] }}"
                           class="flex items-center gap-4 border-b border-gray-100 px-4 py-3.5 transition last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/60">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 text-indigo-500 dark:from-indigo-900/40 dark:to-indigo-900/10 dark:text-indigo-300">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate font-bold text-gray-900 dark:text-white">Pedido #{{ $o['consecutive'] }}</p>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $o['badge']['color'] }}">{{ $o['badge']['label'] }}</span>
                                </div>
                                <p class="mt-0.5 truncate text-xs text-gray-400 dark:text-gray-500">
                                    {{ \Carbon\Carbon::parse($o['date'])->translatedFormat('d M Y') }}
                                    · {{ $o['items'] }} {{ \Illuminate\Support\Str::plural('producto', $o['items']) }}
                                    @if ($o['invoice_no']) · Factura {{ $o['invoice_no'] }} @endif
                                </p>
                            </div>
                            <div class="hidden text-right sm:block">
                                <p class="font-extrabold text-gray-900 dark:text-white">${{ number_format($o['total'], 0, ',', '.') }}</p>
                                @if ($o['delivery'])
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">Entrega {{ \Carbon\Carbon::parse($o['delivery'])->translatedFormat('d M') }}</p>
                                @endif
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
