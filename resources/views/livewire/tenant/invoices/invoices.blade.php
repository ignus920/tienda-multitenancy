<div class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">

    {{-- ===== ENCABEZADO ===== --}}
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-slate-700">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Facturas</h1>
                <p class="text-gray-600 dark:text-slate-400 text-sm mt-1">Gestión y seguimiento de facturas electrónicas</p>
            </div>
        </div>
    </div>

    {{-- ===== BARRA DE HERRAMIENTAS ===== --}}
    <div class="bg-white dark:bg-slate-800 rounded-lg p-4 mb-6 border border-gray-200 dark:border-slate-700">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            {{-- Buscador --}}
            <div class="flex-1 max-w-lg">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" wire:model.live="search"
                        placeholder="Buscar por número, cliente, remisión..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-gray-900 dark:text-slate-200 placeholder-gray-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            {{-- Per page --}}
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-700 dark:text-gray-300">Mostrar:</label>
                <select wire:model.live="perPage"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="12">12</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>

    {{-- ===== TABLA (DESKTOP) ===== --}}
    <div class="bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700">
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/50">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('vnt_invoices.invoiceNumber')">
                            <div class="flex items-center gap-1">
                                FACTURA #
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer"
                            wire:click="sortBy('vnt_invoices.created_at')">
                            <div class="flex items-center gap-1">
                                FECHA
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">REMISIÓN(ES)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">CLIENTE</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">TIPO</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">TOTAL</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">ESTADO</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">PAGO</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        @php($paymentStatus = strtoupper(trim((string) $invoice->status_payment)))
                        <tr class="border-b border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">

                            {{-- FACTURA # --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $invoice->invoiceNumber ?: '#' . $invoice->consecutive }}
                                </span>
                            </td>

                            {{-- FECHA --}}
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300">
                                <div class="font-medium">{{ $invoice->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $invoice->created_at->format('H:i') }}</div>
                            </td>

                            {{-- REMISIÓN(ES) --}}
                            <td class="px-4 py-4 text-sm text-gray-600 dark:text-slate-400">
                                @if($invoice->remission_consecutive)
                                    <span class="font-mono text-xs">{{ $invoice->remission_consecutive }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- CLIENTE --}}
                            <td class="px-4 py-4 text-sm text-gray-700 dark:text-slate-300 max-w-[180px]">
                                <span class="truncate block" title="{{ trim($invoice->client_name) }}">
                                    {{ trim($invoice->client_name) ?: 'N/A' }}
                                </span>
                            </td>

                            {{-- TIPO --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                    {{ $invoice->tipo_factura === 'REMISIONADA'
                                        ? 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300'
                                        : 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300' }}">
                                    {{ $invoice->tipo_factura }}
                                </span>
                            </td>

                            {{-- TOTAL --}}
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white text-right">
                                ${{ number_format($invoice->total_con_impuestos, 0, ',', '.') }}
                            </td>

                            {{-- ESTADO FACTURA --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($invoice->status === 'FACTURADO') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                    @elseif($invoice->status === 'SIN EMITIR') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                    @elseif($invoice->status === 'ANULADO') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                    @else bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 @endif">
                                    {{ $invoice->status }}
                                </span>
                            </td>

                            {{-- ESTADO PAGO --}}
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    @if($paymentStatus === 'PAGADO') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300
                                    @elseif($paymentStatus === 'ABONO') bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300
                                    @elseif($paymentStatus === 'ANULADO') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                    @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 @endif">
                                    <span class="{{ $paymentStatus === 'PAGADO' ? 'text-green-700 dark:text-green-300' : '' }}">
                                        {{ $invoice->status_payment }}
                                    </span>
                                </span>
                            </td>

                            {{-- ACCIONES --}}
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <div x-data="{ open: false, top: 0, left: 0 }"
                                     @scroll.window="open = false"
                                     class="relative inline-block text-left">
                                    <button @click="
                                            open = !open;
                                            $nextTick(() => {
                                                const rect = $el.getBoundingClientRect();
                                                top = rect.bottom + window.scrollY;
                                                left = rect.right - 192;
                                            });"
                                        class="flex items-center text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-lg p-1 transition-colors">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>
                                    <div x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        @click.outside="open = false"
                                        class="fixed w-48 rounded-lg shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 dark:ring-gray-700 z-[9999]"
                                        :style="`top: ${top}px; left: ${left}px;`"
                                        style="display: none;">
                                        <div class="py-1">

                                            {{-- Imprimir (solo FACTURADO) --}}
                                            @if($invoice->status === 'FACTURADO')
                                                <button wire:click="printInvoice({{ $invoice->id }})" @click="open = false"
                                                    wire:loading.attr="disabled" wire:target="printInvoice({{ $invoice->id }})"
                                                    class="w-full text-left px-4 py-2 text-sm text-green-800 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors flex items-center gap-2">
                                                    <svg wire:loading.remove wire:target="printInvoice({{ $invoice->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                    <svg wire:loading wire:target="printInvoice({{ $invoice->id }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                                    Imprimir / PDF
                                                </button>
                                            @endif

                                            {{-- Emitir (solo SIN EMITIR) --}}
                                            @if($invoice->status === 'SIN EMITIR')
                                                <button wire:click="emitirFactura({{ $invoice->id }})" @click="open = false"
                                                    wire:loading.attr="disabled" wire:target="emitirFactura({{ $invoice->id }})"
                                                    class="w-full text-left px-4 py-2 text-sm text-yellow-800 dark:text-yellow-300 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-colors flex items-center gap-2">
                                                    <svg wire:loading.remove wire:target="emitirFactura({{ $invoice->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                    <svg wire:loading wire:target="emitirFactura({{ $invoice->id }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                                    Emitir Factura
                                                </button>
                                            @endif

                                            {{-- Pagar (solo FACTURADO y no PAGADO) --}}
                                            @if($invoice->status === 'FACTURADO' && $paymentStatus !== 'PAGADO' && $paymentStatus !== 'ANULADO')
                                                <button wire:click="payInvoice({{ $invoice->id }})" @click="open = false"
                                                    wire:loading.attr="disabled" wire:target="payInvoice({{ $invoice->id }})"
                                                    class="w-full text-left px-4 py-2 text-sm text-indigo-800 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors flex items-center gap-2">
                                                    <svg wire:loading.remove wire:target="payInvoice({{ $invoice->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                    <svg wire:loading wire:target="payInvoice({{ $invoice->id }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                                    Registrar Pago
                                                </button>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400 dark:text-slate-500">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="font-medium italic">No se encontraron facturas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ===== TARJETAS (MOBILE) ===== --}}
        <div class="lg:hidden p-4 space-y-4">
            @forelse($invoices as $invoice)
                @php($paymentStatus = strtoupper(trim((string) $invoice->status_payment)))
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">

                    {{-- Header --}}
                    <div class="bg-slate-900 dark:bg-slate-950 px-4 py-3 flex justify-between items-center">
                        <div>
                            <span class="text-white font-bold text-base">
                                {{ $invoice->invoiceNumber ?: 'Consec. #' . $invoice->consecutive }}
                            </span>
                        </div>
                        <div class="text-right">
                            <p class="text-[11px] font-bold text-slate-400 uppercase">{{ $invoice->created_at->format('d/m/Y') }}</p>
                            <p class="text-[10px] text-slate-500">{{ $invoice->created_at->format('H:i') }}</p>
                        </div>
                    </div>

                    <div class="p-4 space-y-3">

                        {{-- Cliente --}}
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">CLIENTE</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ trim($invoice->client_name) ?: 'N/A' }}</p>
                        </div>

                        {{-- Remisión / Tipo / Total --}}
                        <div class="grid grid-cols-3 gap-3 pt-2 border-t border-gray-50 dark:border-slate-700/50">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">REMISIÓN(ES)</p>
                                <p class="text-xs text-gray-700 dark:text-slate-300 font-mono">{{ $invoice->remission_consecutive ?: '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">TIPO</p>
                                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full
                                    {{ $invoice->tipo_factura === 'REMISIONADA'
                                        ? 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300'
                                        : 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300' }}">
                                    {{ $invoice->tipo_factura }}
                                </span>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">TOTAL</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">${{ number_format($invoice->total_con_impuestos, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        {{-- Estados --}}
                        <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-50 dark:border-slate-700/50">
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">ESTADO</p>
                                <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-full
                                    @if($invoice->status === 'FACTURADO') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                    @elseif($invoice->status === 'SIN EMITIR') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                    @elseif($invoice->status === 'ANULADO') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                    @else bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 @endif">
                                    {{ $invoice->status }}
                                </span>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">PAGO</p>
                                <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-full
                                    @if($paymentStatus === 'PAGADO') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300
                                    @elseif($paymentStatus === 'ABONO') bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300
                                    @elseif($paymentStatus === 'ANULADO') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                    @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 @endif">
                                    <span class="{{ $paymentStatus === 'PAGADO' ? 'text-green-700 dark:text-green-300' : '' }}">
                                        {{ $invoice->status_payment }}
                                    </span>
                                </span>
                            </div>
                        </div>

                        {{-- Acciones --}}
                        <div class="flex items-center gap-2 pt-3 border-t border-gray-100 dark:border-slate-700">
                            @if($invoice->status === 'FACTURADO')
                                <button wire:click="printInvoice({{ $invoice->id }})"
                                    wire:loading.attr="disabled" wire:target="printInvoice({{ $invoice->id }})"
                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white rounded-xl py-2.5 text-xs font-bold flex items-center justify-center gap-2 transition-all active:scale-95 disabled:opacity-50">
                                    <svg wire:loading.remove wire:target="printInvoice({{ $invoice->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    <svg wire:loading wire:target="printInvoice({{ $invoice->id }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    PDF
                                </button>
                            @endif

                            @if($invoice->status === 'SIN EMITIR')
                                <button wire:click="emitirFactura({{ $invoice->id }})"
                                    wire:loading.attr="disabled" wire:target="emitirFactura({{ $invoice->id }})"
                                    class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl py-2.5 text-xs font-bold flex items-center justify-center gap-2 transition-all active:scale-95 disabled:opacity-50">
                                    <svg wire:loading.remove wire:target="emitirFactura({{ $invoice->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    <svg wire:loading wire:target="emitirFactura({{ $invoice->id }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Emitir
                                </button>
                            @endif

                            @if($invoice->status === 'FACTURADO' && $paymentStatus !== 'PAGADO' && $paymentStatus !== 'ANULADO')
                                <button wire:click="payInvoice({{ $invoice->id }})"
                                    wire:loading.attr="disabled" wire:target="payInvoice({{ $invoice->id }})"
                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 text-xs font-bold flex items-center justify-center gap-2 transition-all active:scale-95 disabled:opacity-50">
                                    <svg wire:loading.remove wire:target="payInvoice({{ $invoice->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    <svg wire:loading wire:target="payInvoice({{ $invoice->id }})" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Pagar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-slate-800 p-12 text-center rounded-xl border-2 border-dashed border-gray-100 dark:border-slate-700">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500 font-medium italic">No se encontraron facturas</p>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        @if($invoices->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    {{-- Toast listeners (heredados del sistema global) --}}
    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            window.addEventListener('open-invoice-pdf', (event) => {
                const data = event.detail;
                const payload = Array.isArray(data) ? data[0] : data;
                if (payload.url) {
                    window.open(payload.url, '_blank');
                }
            });
        });
    </script>
    @endpush
</div>
