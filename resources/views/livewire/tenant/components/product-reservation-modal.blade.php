<div>
    @if($isOpen)
        <!-- Modal Principal -->
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all duration-300" x-data="{ show: true }">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-5xl w-full border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col max-h-[90vh] transition-transform duration-300"
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <!-- Encabezado -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Reservar Producto: 
                            <span class="text-indigo-600 dark:text-indigo-400 font-mono">{{ $productCode }}</span>
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $productName }}</p>
                    </div>
                    <button wire:click="close" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors duration-150 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Cuerpo del Modal (Scrollable) -->
                <div class="p-6 overflow-y-auto space-y-6 flex-1 min-h-[500px]">
                    
                    <!-- Formulario de Registro de Reserva -->
                    <form wire:submit.prevent="save" class="bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700/50 p-5 rounded-xl space-y-4">
                        <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Nueva Reserva</div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Ubicacion stock -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Ubicación Stock *</label>
                                <select wire:model.live="stock_type" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm">
                                    <option value="">Seleccione una opción</option>
                                    <option value="1">En stock</option>
                                    <option value="2">En tránsito</option>
                                </select>
                                @error('stock_type') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Anticipo -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Anticipo *</label>
                                <select wire:model="advance_payment" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm">
                                    <option value="">Seleccione una opción</option>
                                    <option value="6">Sin anticipo</option>
                                    <option value="11">Anticipo 50%</option>
                                    <option value="17">Anticipo mayor al 50%</option>
                                </select>
                                @error('advance_payment') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Cantidad -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Cantidad *</label>
                                <input type="number" wire:model="quantity" min="1" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm" placeholder="Ej: 10">
                                @error('quantity') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Fecha de vencimiento -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Fecha de Vencimiento *</label>
                                <input type="date" wire:model="due_date" {{ $stock_type == '1' ? 'readonly' : '' }} class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm {{ $stock_type == '1' ? 'bg-slate-100 dark:bg-slate-700 cursor-not-allowed' : '' }}">
                                @error('due_date') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Cliente (Estilo Select2 con buscador incorporado) -->
                            <div class="relative transition-all duration-200" x-data="{ open: false }" @click.away="open = false">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Cliente *</label>
                                
                                <!-- Botón que simula el selector -->
                                <button type="button" @click="open = !open" class="w-full flex items-center justify-between bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-750 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 dark:text-white shadow-sm hover:border-slate-400 dark:hover:border-slate-600 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                    <span class="truncate font-medium text-slate-700 dark:text-slate-200">
                                        {{ $selectedCustomerName ?: 'Seleccione una opción' }}
                                    </span>
                                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Dropdown con buscador y lista de clientes -->
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-30 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg shadow-xl overflow-hidden flex flex-col max-h-72"
                                     x-cloak>
                                    
                                    <!-- Buscador dentro del dropdown -->
                                    <div class="p-2 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 flex-shrink-0">
                                        <div class="relative">
                                            <input type="text" 
                                                   wire:model.live="customerSearch" 
                                                   class="w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-indigo-500 focus:border-indigo-500 text-xs py-2 pl-3 pr-8 focus:outline-none" 
                                                   placeholder="Escriba para buscar cliente..."
                                                   @click.stop>
                                            <div class="absolute right-2.5 top-2.5 text-slate-400 dark:text-slate-500">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lista de clientes -->
                                    <div class="overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700 max-h-52">
                                        <!-- Opción por defecto (Seleccione una opción) -->
                                        <button type="button" @click="open = false" wire:click="clearCustomer" class="w-full text-left px-4 py-2.5 text-xs text-rose-500 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150 font-medium">
                                            Seleccione una opción (Limpiar selección)
                                        </button>

                                        @forelse($customers as $c)
                                            <button type="button" @click="open = false" wire:click="selectCustomer({{ $c->id }}, '{{ addslashes($c->customer_name) }}')" class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-xs text-slate-800 dark:text-slate-200 transition-colors duration-150">
                                                <div class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $c->customer_name }}</div>
                                                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 font-mono">NIT/Identificación: {{ $c->identification }}</div>
                                            </button>
                                        @empty
                                            <div class="px-4 py-6 text-center text-xs text-slate-400 dark:text-slate-500">
                                                No se encontraron clientes
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                                
                                @error('customer_id') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Observaciones -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Observaciones iniciales *</label>
                                <input type="text" wire:model="description" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm" placeholder="Ej: Entrega urgente, requiere llamada previa">
                                @error('description') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Información de Embarques en Tránsito Activos -->
                        @if ($stock_type == '2' && !empty($transitImports))
                            <div class="mt-4 p-4 bg-indigo-50/70 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900 rounded-xl space-y-2">
                                <div class="text-xs font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Embarques activos en Tránsito
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs text-indigo-950 dark:text-indigo-200">
                                        <thead>
                                            <tr class="border-b border-indigo-100 dark:border-indigo-900 font-semibold">
                                                <th class="py-1 pr-4">Operación #</th>
                                                <th class="py-1 pr-4 text-center">Unidades</th>
                                                <th class="py-1">Llegada Estimada (ETD)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-indigo-100/50 dark:divide-indigo-900/50 font-mono">
                                            @foreach ($transitImports as $ti)
                                                @php
                                                    $tiData = (object)$ti;
                                                @endphp
                                                <tr>
                                                    <td class="py-1.5 pr-4 font-semibold">{{ $tiData->operation_number ?? 'N/A' }}</td>
                                                    <td class="py-1.5 pr-4 text-center">{{ number_format($tiData->qty_requested ?? 0) }}</td>
                                                    <td class="py-1.5 font-semibold text-slate-700 dark:text-slate-300">
                                                        {{ $tiData->etd ? \Carbon\Carbon::parse($tiData->etd)->format('d/m/Y') : 'Por confirmar' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <!-- Botones de Guardar / Cancelar -->
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-all duration-150 flex items-center gap-1.5 focus:outline-none">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                Guardar Reserva
                            </button>
                        </div>
                    </form>

                    <!-- Tabla de Reservas Registradas -->
                    <div class="space-y-3">
                        <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Historial de Reservas</div>
                        
                        <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-900">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300 border-collapse">
                                    <thead class="bg-slate-50 dark:bg-slate-800 text-xs font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                        <tr>
                                            <th class="px-4 py-3">Cant</th>
                                            <th class="px-4 py-3">Cliente</th>
                                            <th class="px-4 py-3">Fecha Vence</th>
                                            <th class="px-4 py-3">Anticipo</th>
                                            <th class="px-4 py-3">Estado</th>
                                            <th class="px-4 py-3">Ubicación</th>
                                            <th class="px-4 py-3">Observaciones</th>
                                            <th class="px-4 py-3">Creado por</th>
                                            <th class="px-4 py-3 text-right">Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @forelse($reservations as $r)
                                            @php
                                                $isExpiringSoon = \Carbon\Carbon::parse($r->due_date)->subDay()->isToday();
                                                $rowBg = $isExpiringSoon 
                                                    ? 'bg-rose-50 dark:bg-rose-950/20 text-rose-950 dark:text-rose-100 border-l-4 border-rose-500 font-semibold' 
                                                    : 'hover:bg-slate-50/50 dark:hover:bg-slate-800/40';
                                            @endphp
                                            <tr class="{{ $rowBg }} transition-colors duration-150">
                                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $r->quantity }}</td>
                                                <td class="px-4 py-3">
                                                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ $r->customer->businessName ?? 'N/A' }}</div>
                                                    <div class="text-xs text-slate-400">{{ $r->customer->identification ?? '' }}</div>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($r->due_date)->format('d/m/Y') }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    @if($r->advance_payment == '6')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">Sin anticipo</span>
                                                    @elseif($r->advance_payment == '11')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Anticipo 50%</span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Anticipo >50%</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    @php
                                                        $statusClasses = [
                                                            1 => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400', // Registrado
                                                            2 => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400', // Vendido
                                                            3 => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400', // Vencido
                                                            4 => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400', // Anulado
                                                        ];
                                                        $cls = $statusClasses[$r->status_id] ?? 'bg-slate-100 text-slate-800';
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $cls }}">
                                                        {{ $r->status->name ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-xs">
                                                    {{ $r->stock_type == '1' ? 'En Stock' : 'En Tránsito' }}
                                                </td>
                                                <td class="px-4 py-3 max-w-[200px] truncate text-xs" title="{{ $r->description }} {{ $r->obs ? '| Obs: ' . $r->obs : '' }}">
                                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $r->description }}</span>
                                                    @if($r->obs)
                                                        <span class="text-slate-400 block mt-0.5 italic text-[11px] truncate">Modificación: {{ $r->obs }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-xs whitespace-nowrap">
                                                    {{ $r->user->name ?? 'N/A' }}
                                                </td>
                                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                                    @if($r->status_id == 1)
                                                        <button type="button" wire:click="openEditStatus({{ $r->id }})" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-indigo-500 hover:text-indigo-700 dark:hover:text-indigo-400 transition-colors duration-150 inline-flex items-center gap-1 text-xs font-medium focus:outline-none">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                            Cambiar Estado
                                                        </button>
                                                    @else
                                                        <span class="text-xs text-slate-400 dark:text-slate-500 italic">Finalizado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="px-4 py-8 text-center text-slate-400 dark:text-slate-500">
                                                    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-700 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4.5m16 3H4" />
                                                    </svg>
                                                    No se han registrado reservas para este producto.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            @if($reservations->hasPages())
                                <div class="px-4 py-3 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700">
                                    {{ $reservations->links() }}
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- Footer del Modal Principal -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                    <button type="button" wire:click="close" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 font-semibold text-sm hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-150 focus:outline-none">
                        Cerrar
                    </button>
                </div>

            </div>
        </div>
    @endif

    <!-- Submodal para Editar Estado -->
    @if($showEditStatusModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm transition-all duration-200" x-data="{ show: true }">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-lg w-full border border-slate-200 dark:border-slate-700 overflow-hidden flex flex-col transition-transform duration-200"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                    <h4 class="text-md font-bold text-slate-800 dark:text-white flex items-center gap-1.5">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Cambio de Estado
                    </h4>
                    <button type="button" wire:click="closeEditStatus" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors duration-150 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="updateStatus" class="p-5 space-y-4">
                    <div class="bg-indigo-50 dark:bg-indigo-950/40 border-l-4 border-indigo-500 p-3 rounded-r-lg text-xs text-indigo-700 dark:text-indigo-300 font-medium">
                        "Si la reserva es vendida registre la OP correspondiente, si es anulada justifique la anulación."
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Estado *</label>
                        <select wire:model="newStatusId" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm">
                            <option value="">Seleccione una opción</option>
                            @foreach($statuses as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                        @error('newStatusId') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            @if($newStatusId == 2)
                                Registrar la OP *
                            @elseif($newStatusId == 4)
                                Justificar anulación *
                            @else
                                Observaciones
                            @endif
                        </label>
                        <input type="text" wire:model="statusObservations" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-sm" placeholder="{{ $newStatusId == 2 ? 'Ingrese número de OP' : ($newStatusId == 4 ? 'Describa el motivo de la anulación' : 'Observaciones') }}">
                        @error('statusObservations') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" wire:click="closeEditStatus" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-700 dark:text-slate-300 font-semibold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors duration-150 focus:outline-none">
                            Cerrar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-colors duration-150 focus:outline-none">
                            Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
