@php
    $header = 'Gestión de Ventas';
@endphp

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="{ showFollowupModal: false }">
    <div class="max-w-[1600px] mx-auto">
        <!-- Header & Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Gestión de Ventas</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Seguimiento y control de cotizaciones</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 flex-1 lg:max-w-4xl">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Desde</label>
                            <input type="date" wire:model.live="fechaIni" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Hasta</label>
                            <input type="date" wire:model.live="fechaFin" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Asesor</label>
                            <select wire:model.live="advisorId" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                                <option value="0">Todos los asesores</option>
                                @foreach($advisors as $advisor)
                                    <option value="{{ $advisor->id }}">{{ $advisor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Filtrar</label>
                            <select wire:model.live="filterStatus" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                                <option value="0">Todos los estados</option>
                                <option value="1">Sin Ventas</option>
                                <option value="2">Con Pendientes</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Panel: Resumen de Clientes -->
            <div class="lg:col-span-5">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Resumen por Cliente</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase"># Cot</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase"># Pend</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($clients as $client)
                                    <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors {{ $selectedClientId == $client->client_id ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $client->client_name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Asesor: {{ \App\Models\Auth\User::on('central')->find($client->userId)?->name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                                {{ $client->total_quotes }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $client->pending_quotes > 0 ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' }}">
                                                {{ $client->pending_quotes }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <button wire:click="selectClient({{ $client->client_id }}, '{{ $client->client_name }}')" class="p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-all">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">No se encontraron registros</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Cotizaciones del Cliente -->
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 h-full overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                            @if($selectedClientId)
                                Cotizaciones de: <span class="text-indigo-600 dark:text-indigo-400">{{ $selectedClientName }}</span>
                            @else
                                Seleccione un cliente para ver detalles
                            @endif
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($selectedClientId)
                            <div class="grid grid-cols-1 gap-4">
                                @forelse($quotes as $quote)
                                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-500 transition-all shadow-sm">
                                        <div class="flex flex-col md:flex-row justify-between gap-4">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">#{{ $quote->consecutive }}</span>
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ in_array($quote->status, ['FACTURADO', 'REMISIN']) ? 'bg-green-100 text-green-800 dark:bg-green-900/30' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30' }}">
                                                        {{ $quote->status }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">{{ $quote->created_at->format('d/m/Y h:i A') }}</span>
                                                </div>
                                                <div class="grid grid-cols-2 gap-x-4 text-sm">
                                                    <p class="text-gray-600 dark:text-gray-400"><span class="font-semibold">Asesor:</span> {{ $quote->seller_name }}</p>
                                                    <p class="text-gray-600 dark:text-gray-400"><span class="font-semibold">Bodega:</span> {{ $quote->getStorageName() }}</p>
                                                    <p class="text-gray-600 dark:text-gray-400 mt-1 col-span-2 italic">"{{ $quote->observations ?: 'Sin observaciones' }}"</p>
                                                </div>
                                            </div>
                                            <div class="flex flex-row md:flex-col justify-end gap-2">
                                                <button wire:click="openFollowupModal({{ $quote->id }})" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-lg shadow-indigo-500/30">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                    </svg>
                                                    Seguimiento
                                                </button>
                                                <button wire:click="openHistoryModal({{ $quote->id }})" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    Ver Historial
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Historial de Seguimiento (Pequeño resumen) -->
                                        @php $history = $this->getQuoteHistory($quote->id); @endphp
                                        @if($history->count() > 0)
                                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                                <div class="flex items-center justify-between mb-2">
                                                    <button wire:click="openHistoryModal({{ $quote->id }})" class="text-[10px] font-bold text-indigo-400 hover:text-indigo-600 uppercase tracking-widest flex items-center transition-colors">
                                                        <span>Últimos Seguimientos</span>
                                                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    </button>
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold {{ $history->first()->status->class_color }} text-white">
                                                        {{ $history->first()->status->name }}
                                                    </span>
                                                </div>
                                                <div class="space-y-2">
                                                    @foreach($history->take(2) as $log)
                                                        <div class="text-xs flex gap-2">
                                                            <span class="text-gray-400 whitespace-nowrap">{{ $log->created_at?->format('d/m') ?? 'N/A' }}:</span>
                                                            <span class="text-gray-700 dark:text-gray-300 line-clamp-1">{{ $log->comment }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-center py-12">
                                        <p class="text-gray-500">No hay cotizaciones para este cliente en el rango seleccionado.</p>
                                    </div>
                                @endforelse
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-24 opacity-50">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path>
                                </svg>
                                <p class="text-gray-500 font-medium">Seleccione un cliente a la izquierda para ver su historial</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Seguimiento -->
    <x-modal name="modalGestion" maxWidth="2xl">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Seguimiento</h3>
            <button x-on:click="$dispatch('close-modal', 'modalGestion')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6">
            <form wire:submit.prevent="saveFollowup" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Comentario(*):</label>
                        <input type="text" wire:model="comment" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white" placeholder="Observacion">
                        @error('comment') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Estado:</label>
                        <select wire:model="followupStatusId" class="w-full bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-xl p-3 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                            <option value="">Seleccione...</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                            @endforeach
                        </select>
                        @error('followupStatusId') <span class="text-red-500 text-[10px] mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" x-on:click="$dispatch('close-modal', 'modalGestion')" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold rounded-lg text-xs transition-colors shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                        Cerrar
                    </button>
                    <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs transition-colors shadow-lg shadow-indigo-500/30">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Guardar
                    </button>
                </div>
            </form>

            <!-- Tabla de items -->
            <div class="mt-8 border-t border-gray-100 dark:border-gray-700 pt-6">
                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Descripcion</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Precio</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($quoteItems as $item)
                                <tr class="text-sm hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium">{{ $item['description'] }}</td>
                                    <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                        ${{ number_format($item['value'], 0) }} x {{ number_format($item['quantity'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">
                                        ${{ number_format($item['value'] * $item['quantity'], 0) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-gray-500 italic">No hay productos en esta cotización</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-modal>

    <!-- Modal de Historial (Imagen 4) -->
    <x-modal name="modalHistorial" maxWidth="xl">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Comentarios</h3>
            <button x-on:click="$dispatch('close-modal', 'modalHistorial')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6">
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase">Observacion</th>
                            <th class="px-4 py-3 text-center text-xs font-bold uppercase">Fecha reg</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                        @if($selectedQuoteId)
                            @php $history = $this->getQuoteHistory($selectedQuoteId); @endphp
                            @forelse($history as $log)
                                <tr class="text-sm hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                    <td class="px-4 py-4 text-gray-700 dark:text-gray-300">{{ $log->comment }}</td>
                                    <td class="px-4 py-4 text-center text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ $log->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-8 text-center text-gray-500 italic">No hay historial para esta cotización</td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
            
            <div class="flex justify-end mt-6">
                <button type="button" x-on:click="$dispatch('close-modal', 'modalHistorial')" class="inline-flex items-center px-6 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-bold rounded-lg text-xs transition-colors shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                    Cerrar
                </button>
            </div>
        </div>
    </x-modal>
        </div>
    </div>
</div>
