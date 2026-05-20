<div class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">
    <!-- Encabezado -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Cartera</h1>
                <p class="text-gray-600 dark:text-slate-400 text-sm mt-1">Gestión de autorizaciones</p>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <!-- Despacho -->
        <div wire:click="setFilter('despacho')"
             style="{{ $activeFilter === 'despacho' ? 'background:#F59E0B;border-color:#D97706;' : '' }}"
             class="p-4 rounded-lg border transition-all duration-300 cursor-pointer hover:shadow-lg {{ $activeFilter === 'despacho' ? 'border-amber-600' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 shadow-sm' }}">
            <div class="flex items-center space-x-4">
                <div style="background:{{ $activeFilter === 'despacho' ? 'rgba(255,255,255,0.2)' : '#F59E0B' }};"
                     class="w-12 h-12 rounded-lg flex items-center justify-center text-xl font-bold text-white">
                    {{ $metrics['despacho'] }}
                </div>
                <p class="text-sm font-semibold {{ $activeFilter === 'despacho' ? 'text-white' : 'text-gray-500 dark:text-slate-400' }}">Despacho</p>
            </div>
        </div>
        <!-- Pago -->
        <div wire:click="setFilter('pago')"
             style="{{ $activeFilter === 'pago' ? 'background:#4F46E5;border-color:#4338CA;' : '' }}"
             class="p-4 rounded-lg border transition-all duration-300 cursor-pointer hover:shadow-lg {{ $activeFilter === 'pago' ? 'border-indigo-700' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 shadow-sm' }}">
            <div class="flex items-center space-x-4">
                <div style="background:{{ $activeFilter === 'pago' ? 'rgba(255,255,255,0.2)' : '#4F46E5' }};"
                     class="w-12 h-12 rounded-lg flex items-center justify-center text-xl font-bold text-white">
                    {{ $metrics['pago'] }}
                </div>
                <p class="text-sm font-semibold {{ $activeFilter === 'pago' ? 'text-white' : 'text-gray-500 dark:text-slate-400' }}">Pago</p>
            </div>
        </div>
        <!-- Empaque -->
        <div wire:click="setFilter('empaque')"
             style="{{ $activeFilter === 'empaque' ? 'background:#22C55E;border-color:#16A34A;' : '' }}"
             class="p-4 rounded-lg border transition-all duration-300 cursor-pointer hover:shadow-lg {{ $activeFilter === 'empaque' ? 'border-green-600' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 shadow-sm' }}">
            <div class="flex items-center space-x-4">
                <div style="background:{{ $activeFilter === 'empaque' ? 'rgba(255,255,255,0.2)' : '#22C55E' }};"
                     class="w-12 h-12 rounded-lg flex items-center justify-center text-xl font-bold text-white">
                    {{ $metrics['empaque'] }}
                </div>
                <p class="text-sm font-semibold {{ $activeFilter === 'empaque' ? 'text-white' : 'text-gray-500 dark:text-slate-400' }}">Empaque</p>
            </div>
        </div>
        <!-- Anulados -->
        <div wire:click="setFilter('anulados')"
             style="{{ $activeFilter === 'anulados' ? 'background:#EF4444;border-color:#DC2626;' : '' }}"
             class="p-4 rounded-lg border transition-all duration-300 cursor-pointer hover:shadow-lg {{ $activeFilter === 'anulados' ? 'border-red-600' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 shadow-sm' }}">
            <div class="flex items-center space-x-4">
                <div style="background:{{ $activeFilter === 'anulados' ? 'rgba(255,255,255,0.2)' : '#EF4444' }};"
                     class="w-12 h-12 rounded-lg flex items-center justify-center text-xl font-bold text-white">
                    {{ $metrics['anulados'] }}
                </div>
                <p class="text-sm font-semibold {{ $activeFilter === 'anulados' ? 'text-white' : 'text-gray-500 dark:text-slate-400' }}">Anulados</p>
            </div>
        </div>
        <!-- Pendientes -->
        <div wire:click="setFilter('pendientes')"
             style="{{ $activeFilter === 'pendientes' ? 'background:#8B5CF6;border-color:#7C3AED;' : '' }}"
             class="p-4 rounded-lg border transition-all duration-300 cursor-pointer hover:shadow-lg {{ $activeFilter === 'pendientes' ? 'border-violet-600' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 shadow-sm' }}">
            <div class="flex items-center space-x-4">
                <div style="background:{{ $activeFilter === 'pendientes' ? 'rgba(255,255,255,0.2)' : '#8B5CF6' }};"
                     class="w-12 h-12 rounded-lg flex items-center justify-center text-xl font-bold text-white">
                    {{ $metrics['pendientes'] }}
                </div>
                <p class="text-sm font-semibold {{ $activeFilter === 'pendientes' ? 'text-white' : 'text-gray-500 dark:text-slate-400' }}">Pendientes</p>
            </div>
        </div>
    </div>

    <!-- Buscador y Filtros -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-t-xl border border-gray-100 dark:border-slate-700">
        <div style="display: flex; align-items: flex-end; gap: 12px;">
            <div style="flex: 1.5; min-width: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Búsqueda rápida</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Búsqueda rápida.." class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm pl-10">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div style="flex: 1; min-width: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">NIT / Cédula</label>
                <input type="text" wire:model.live.debounce.300ms="searchNit" placeholder="Ej: 900.." class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
            </div>
            <div style="flex: 1; min-width: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nombre / Razón Social</label>
                <input type="text" wire:model.live.debounce.300ms="searchName" placeholder="Buscar cliente..." class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
            </div>
            <div style="flex: 1; min-width: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Número Cotización</label>
                <input type="text" wire:model.live.debounce.300ms="searchQuote" placeholder="Ej: COT-123" class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
            </div>
            <div style="flex: 1; min-width: 0;">
                <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-1">desde:</label>
                <input type="date" wire:model.live="fromDate" class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
            </div>
            <div style="flex: 1; min-width: 0;">
                <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-1">hasta:</label>
                <div style="display: flex; gap: 6px; align-items: center;">
                    <input type="date" wire:model.live="toDate" class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
                    <button wire:click="clearFilters" class="p-2 text-gray-400 hover:text-red-500 transition-colors flex-shrink-0" title="Limpiar Filtros">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div style="flex-shrink: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Mostrar</label>
                <select wire:model.live="perPage" class="rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm py-2">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="mt-6 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden transition-colors">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-slate-700">
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">#OP / Cliente</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Estado</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Retenciones</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Formas de pago</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Empaque</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Despacho</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Pago</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">ACCIONES</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                @foreach($remissions as $remission)
                @php
                    $empaque = $remission->authorizations->where('auth_type', 'empaque')->last();
                    $despacho = $remission->authorizations->where('auth_type', 'despacho')->last();
                    $pago = $remission->authorizations->where('auth_type', 'pago')->last();
                @endphp
                <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">#{{ $remission->consecutive }}</span>
                            <span class="text-xs text-gray-400 mb-1">{{ $remission->created_at->format('Y-m-d H:i') }}</span>
                            <span class="text-sm font-medium text-gray-700 dark:text-slate-300 uppercase">{{ $remission->quote->customer_name ?? 'N/A' }}</span>
                            @if($remission->delivery_type)
                            <span class="mt-1 text-[10px] font-bold bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded text-gray-500 w-fit">
                                {{ $remission->delivery_type }}
                            </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase 
                            {{ $remission->status === 'ANULADO' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600' }}">
                            {{ $remission->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="bg-gray-100 dark:bg-slate-900/50 p-2 rounded-lg text-[11px] space-y-1">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total:</span>
                                <span class="font-bold text-indigo-600">${{ number_format($remission->total_rem, 0) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 dark:border-slate-700 pt-1">
                                <span class="text-gray-500">Rfte: ${{ number_format($remission->invoice->retentionFuente ?? 0, 0) }}</span>
                                <span class="text-gray-500">Rtica: ${{ number_format($remission->invoice->retentionIca ?? 0, 0) }}</span>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Formas de pago -->
                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-2">
                            {{-- Método acordado en el pedido (Consistencia con Pedidos) --}}
                            <div class="mb-1 pb-1 border-b border-gray-100 dark:border-slate-700/50">
                                <p class="text-[9px] font-bold text-gray-400 uppercase mb-0.5">Acordado:</p>
                                <div class="flex items-center justify-between text-green-600 dark:text-green-400">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-[11px] font-bold uppercase">{{ $remission->methodPayment->name ?? 'SIN DEFINIR' }}</span>
                                    </div>
                                    @if($remission->proof_payment && (!$remission->invoice || $remission->invoice->payments->count() == 0))
                                        <a href="{{ asset('storage/' . $remission->proof_payment) }}" 
                                           target="_blank" 
                                           class="inline-flex items-center text-[10px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold transition-colors gap-1"
                                           title="Ver soporte de pago de la remisión">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                            Ver Soporte
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- Pagos reales registrados en la factura --}}
                            @if($remission->invoice && $remission->invoice->payments->count() > 0)
                                <p class="text-[9px] font-bold text-gray-400 uppercase mb-0.5">Pagos Recibidos:</p>
                                @foreach($remission->invoice->payments as $payment)
                                    <div class="bg-indigo-50/50 dark:bg-indigo-900/10 p-1.5 rounded border border-indigo-100 dark:border-indigo-900/30 flex flex-col gap-1">
                                        <div>
                                            <p class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 uppercase leading-none mb-1">
                                                {{ $payment->methodPayment->name ?? 'PAGO' }}
                                            </p>
                                            <p class="text-xs font-bold text-gray-700 dark:text-slate-300">
                                                ${{ number_format($payment->value, 0) }}
                                            </p>
                                        </div>
                                        @if($remission->proof_payment || $payment->proof_payment)
                                            <a href="{{ asset('storage/' . ($remission->proof_payment ?? $payment->proof_payment)) }}" 
                                               target="_blank" 
                                               class="inline-flex items-center text-[10px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold mt-0.5 transition-colors gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                                Ver Soporte
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <span class="text-[10px] text-gray-400 italic">Sin pagos registrados</span>
                            @endif
                        </div>
                    </td>

                    <!-- Checkboxes de Autorización -->
                    <td class="px-6 py-4">
                        <div class="flex flex-col items-center space-y-2">
                            <input type="checkbox" 
                                wire:click="toggleAuthorization({{ $remission->id }}, 'empaque')"
                                {{ $empaque && $empaque->status ? 'checked' : '' }}
                                {{ $remission->status === 'ANULADO' ? 'disabled' : '' }}
                                class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500 {{ $remission->status === 'ANULADO' ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                            @if($empaque && $empaque->status)
                            <div class="text-[9px] text-gray-400 text-center leading-tight">
                                <p class="font-bold">{{ $empaque->user->name ?? 'Usuario' }}</p>
                                <p>{{ $empaque->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            @endif
                        </div>
                    </td>
                    
                    <td class="px-6 py-4">
                        <div class="flex flex-col items-center space-y-2">
                            <input type="checkbox" 
                                wire:click="toggleAuthorization({{ $remission->id }}, 'despacho')"
                                {{ $despacho && $despacho->status ? 'checked' : '' }}
                                {{ $remission->status === 'ANULADO' ? 'disabled' : '' }}
                                class="w-5 h-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500 {{ $remission->status === 'ANULADO' ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                            @if($despacho && $despacho->status)
                            <div class="text-[9px] text-gray-400 text-center leading-tight">
                                <p class="font-bold">{{ $despacho->user->name ?? 'Usuario' }}</p>
                                <p>{{ $despacho->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            @endif
                        </div>
                    </td>

                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col items-center space-y-2">
                            <input type="checkbox" 
                                wire:click="toggleAuthorization({{ $remission->id }}, 'pago')"
                                {{ $pago && $pago->status ? 'checked' : '' }}
                                {{ $remission->status === 'ANULADO' ? 'disabled' : '' }}
                                class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 {{ $remission->status === 'ANULADO' ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                            @if($pago)
                            <div class="text-[9px] text-center leading-tight {{ $pago->status ? 'text-gray-400' : 'text-red-500' }}">
                                <p class="font-bold">
                                    {{ $pago->status ? '' : 'Desconfirmado por: ' }}
                                    {{ $pago->user->name ?? 'Usuario' }}
                                </p>
                                <p>{{ $pago->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            @endif
                        </div>
                    </td>
                    
                    <td class="px-6 py-4 text-center">
                        <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
                            <button @click="open = !open" class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>
                            
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-[60]"
                                 style="display: none;">
                                <div class="py-1">
                                    <button wire:click="openObservationsModal({{ $remission->id }})" 
                                            @click="open = false"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        Observaciones
                                    </button>
                                    <button wire:click="openJustificacionModal({{ $remission->id }})" 
                                            @click="open = false"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Agregar observación
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="p-6">
            {{ $remissions->links() }}
        </div>
    </div>

    <!-- Componente de Observaciones -->
    <livewire:tenant.components.observations-modal />

    <!-- Modal de Justificación de Cartera -->
    <template x-teleport="body">
        <div x-show="$wire.showJustificacionModal" 
             class="fixed inset-0 z-[100] overflow-y-auto" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-slate-900/80 transition-opacity" @click="$wire.showJustificacionModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-middle bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-slate-700">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 text-center">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                            {{ $isDesconfirmarPago ? 'Justificación requerida' : 'Agregar observación' }}
                        </h3>
                    </div>

                    <div class="p-6">
                        <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-4 text-center uppercase tracking-wide">
                            {{ $isDesconfirmarPago ? 'Por favor, justifique por qué está desconfirmando el pago:' : 'Observación de Cartera' }}
                        </label>
                        <textarea wire:model="justificacionText" 
                                  rows="4" 
                                  class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="{{ $isDesconfirmarPago ? 'Escribe tu justificación aquí...' : 'Escribe aquí tu comentario...' }}"></textarea>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/50 flex justify-center space-x-3">
                        <button wire:click="saveJustificacion" 
                                class="px-8 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition-colors shadow-lg shadow-blue-600/20">
                            {{ $isDesconfirmarPago ? 'Enviar' : 'Guardar' }}
                        </button>
                        <button @click="$wire.showJustificacionModal = false" 
                                class="px-8 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-bold transition-colors shadow-lg shadow-slate-600/20">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
