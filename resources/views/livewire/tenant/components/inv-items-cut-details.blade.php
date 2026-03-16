<div>
    <template x-teleport="body">
        <div x-data="{ 
            show: @entangle('isOpen').live,
            isNew: @entangle('isNew').live,
            profileLength: @entangle('profileLength').live,
            kerf: @entangle('kerf').live,
            cuts: @entangle('cuts').live,
            remissionId: @entangle('remissionId').live,
            justification: @entangle('justification').live,
            
            get accumulated() {
                let sum = 0;
                let activeCount = 0;
                this.cuts.forEach(c => {
                    let val = parseFloat(c);
                    if (val > 0) {
                        sum += val;
                        activeCount++;
                    }
                });
                return sum + (activeCount * this.kerf);
            },
            
            get remaining() {
                return this.profileLength - this.accumulated;
            }
        }"
             x-show="show"
             x-cloak
             style="display:none;"
             class="fixed inset-0 z-[10000] flex items-center justify-center p-4">

            @if($isOpen)
            <!-- Backdrop con desenfoque Premium -->
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-md transition-opacity" 
                 x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="show = false"></div>

            <!-- Modal Panel -->
            <div class="relative z-10 bg-white dark:bg-gray-800 rounded-2xl w-full max-w-6xl max-h-[90vh] shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop>

                <!-- Header Premium Style -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-indigo-900">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/10 rounded-lg">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758l5.758-5.758"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-white uppercase tracking-wider">
                            Detalles de Corte de Ítems
                        </h3>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <!-- Bloque de Conversión Permanente -->
                        <div class="hidden sm:flex items-center gap-4 text-[10px] font-black text-indigo-100 bg-white/10 px-4 py-1.5 rounded-full border border-white/20 shadow-inner">
                            <div class="flex items-center gap-1.5">
                                <span class="opacity-60 uppercase">Manual:</span>
                                <span>1000mm = 100cm</span>
                                <div class="w-1 h-1 bg-indigo-400 rounded-full mx-1"></div>
                                <span>3050mm = 305cm</span>
                            </div>
                        </div>

                        <button @click="show = false" class="text-white/80 hover:text-white transition-colors p-1 hover:bg-white/10 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="flex-1 overflow-y-auto custom-scrollbar bg-gray-50/50 dark:bg-gray-900/50">
                    
                    <!-- VISTA LISTADO AGRUPADO -->
                    <div x-show="!isNew" class="p-6 h-full flex flex-col gap-6">
                        
                        <!-- Header Actions & Group Selector -->
                        <div class="flex flex-col gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase flex items-center gap-2">
                                        # Cortes
                                    </h4>
                                </div>
                                <div class="flex items-center gap-3">
                            @if($selectedCutGroupId)
                                <button onclick="window.open('{{ route('tenant.components.inv-items-cut-details.print', ['cutId' => $selectedCutGroupId]) }}', '_blank')" class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all shadow-md group">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    <span class="font-bold uppercase text-xs">Imprimir</span>
                                </button>
                            @endif

                            <button wire:click="openCreate" class="flex items-center gap-2 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all shadow-md group">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Nuevo Registro
                                    </button>
                                </div>
                            </div>

                            <div class="w-full relative">
                                <select wire:model.live.debounce.300ms="selectedCutGroupId" class="w-full bg-white dark:bg-gray-900 border border-indigo-200 dark:border-indigo-800/50 text-gray-800 dark:text-gray-200 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 shadow-sm appearance-none font-medium cursor-pointer">
                                    <option value="">-- Seleccione un grupo de corte --</option>
                                    @foreach($cutGroups as $group)
                                        <option value="{{ $group->cut_id }}">
                                            ( # {{ $group->cut_id }} ) {{ Carbon\Carbon::parse($group->created_at)->format('Y-m-d') }} / {{ mb_strtoupper($group->customer->firstName ?? '') }} {{ mb_strtoupper($group->customer->lastName ?? '') }}  {{ empty($group->customer->firstName) && empty($group->customer->lastName) ? 'SIN CLIENTE' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-indigo-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Data Table (Agrupada) -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <div class="overflow-x-auto custom-scrollbar">
                                <table class="w-full text-left whitespace-nowrap min-w-max border-collapse">
                                    <thead>
                                        <tr class="bg-[#1f2937] text-white text-[11px] font-bold tracking-wider border-b border-gray-700">
                                            <th class="px-4 py-3">Ref</th>
                                            <th class="px-4 py-3">#op</th>
                                            <th class="px-4 py-3"># de Perfiles</th>
                                            <th class="px-4 py-3">Largo perfil</th>
                                            <th class="px-4 py-3">Acumulado</th>
                                            <th class="px-4 py-3">Sobrante</th>
                                            <th class="px-4 py-3">Observaciones</th>
                                            <th class="px-4 py-3">Opciones</th>
                                        </tr>
                                    </thead>
                                    
                                    @forelse($cutDetails as $detail)
                                    <tbody class="border-b-2 border-gray-200 dark:border-gray-700/50 hover:bg-gray-50/30 dark:hover:bg-gray-800/30 transition-colors">
                                        <!-- Fila Principal (Info del Perfil) -->
                                        <tr>
                                            <td class="px-4 py-3 bg-[#f8fafc] dark:bg-gray-900 border-r border-[#e2e8f0] dark:border-gray-700">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $detail->item->internal_code ?? '' }}-{{ $detail->item->name ?? '' }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 bg-[#f8fafc] dark:bg-gray-900 border-r border-[#e2e8f0] dark:border-gray-700 font-mono text-xs text-gray-600 dark:text-gray-400">
                                                {{ $detail->productionOrder->consecutive ?? '' }}
                                            </td>
                                            <td class="px-4 py-3 bg-[#f8fafc] dark:bg-gray-900 border-r border-[#e2e8f0] dark:border-gray-700 text-sm font-medium dark:text-gray-300">
                                                {{ $detail->repeat_in }}
                                            </td>
                                            <td class="px-4 py-3 bg-[#f8fafc] dark:bg-gray-900 border-r border-[#e2e8f0] dark:border-gray-700">
                                                <div class="flex flex-col gap-1 text-xs text-gray-600 dark:text-gray-400">
                                                    <span>{{ $detail->length_cm }} cm</span>
                                                    <span>{{ $detail->length_mm }} mm</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 bg-[#f8fafc] dark:bg-gray-900 border-r border-[#e2e8f0] dark:border-gray-700">
                                                <div class="flex flex-col gap-1 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                                    <span>{{ rtrim(rtrim(number_format($detail->accumulated_cm, 2, '.', ''), '0'), '.') }} cm</span>
                                                    <span>{{ rtrim(rtrim(number_format($detail->accumulated, 2, '.', ''), '0'), '.') }} mm</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 bg-[#f8fafc] dark:bg-gray-900 border-r border-[#e2e8f0] dark:border-gray-700">
                                                <div class="flex flex-col gap-1 text-xs font-bold {{ $detail->remaining_cm > 10 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                                    <span>{{ rtrim(rtrim(number_format($detail->remaining_cm, 2, '.', ''), '0'), '.') }} cm</span>
                                                    <span>{{ rtrim(rtrim(number_format($detail->remaining, 2, '.', ''), '0'), '.') }} mm</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 bg-[#f8fafc] dark:bg-gray-900 border-r border-[#e2e8f0] dark:border-gray-700 text-xs text-gray-500 truncate max-w-[150px]" title="{{ $detail->notes }}">
                                                {{ $detail->notes }}
                                            </td>
                                            <td class="px-4 py-3 bg-[#f8fafc] dark:bg-gray-900">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-1 bg-[#22c55e] text-white text-[10px] font-bold rounded">Registrado</span>
                                                    <button class="p-1 bg-[#ef4444] hover:bg-red-600 text-white rounded transition-colors shadow-sm active:scale-95" title="Eliminar registro">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Fila de Cortes (CM) -->
                                        <tr>
                                            <td class="px-4 py-2 font-bold text-sm text-gray-700 dark:text-gray-300 border-r border-[#e2e8f0] dark:border-gray-700 bg-white dark:bg-gray-800">
                                                cm
                                            </td>
                                            <td colspan="7" class="px-4 py-2 bg-white dark:bg-gray-800 relative">
                                                <div class="flex flex-wrap items-center gap-2 max-w-full">
                                                    @foreach(explode(', ', $detail->plan_centimeters) as $cm)
                                                        @if(trim($cm) !== '')
                                                            <div class="px-4 py-1.5 border border-gray-800 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-900 shadow-sm min-w-[3rem] text-center">
                                                                {{ trim($cm) }}
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Fila de Cortes (MM) -->
                                        <tr>
                                            <td class="px-4 py-2 font-bold text-sm text-gray-700 dark:text-gray-300 border-r border-[#e2e8f0] dark:border-gray-700 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700/50">
                                                mm
                                            </td>
                                            <td colspan="7" class="px-4 py-2 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700/50 relative">
                                                <div class="flex flex-wrap items-center gap-2 max-w-full">
                                                    @foreach(explode(', ', $detail->plan_millimeters) as $mm)
                                                        @if(trim($mm) !== '')
                                                            <div class="px-4 py-1.5 border border-gray-800 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-900 shadow-sm min-w-[3rem] text-center">
                                                                {{ trim($mm) }}
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    @empty
                                    <tbody>
                                        <tr>
                                            <td colspan="8" class="px-6 py-12 text-center text-gray-500 bg-white dark:bg-gray-800">
                                                No hay registros en este grupo de corte. Puede crear uno nuevo.
                                            </td>
                                        </tr>
                                    </tbody>
                                    @endforelse
                                </table>
                            </div>
                        </div>

                        <!-- Paginación si corresponde -->
                        @if($cutDetails->hasPages())
                        <div class="mt-4">
                            {{ $cutDetails->links() }}
                        </div>
                        @endif
                    </div>                    <!-- VISTA FORMULARIO NUEVO -->
                    <div x-show="isNew" class="p-6 h-full flex flex-col gap-6" x-transition>
                        <div class="flex items-center justify-between">
                            <button @click="isNew = false" class="text-sm font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-2 hover:underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                Volver al listado
                            </button>
                        </div>

                        <div class="grid grid-cols-12 gap-6">
                            <!-- Sidebar Form -->
                            <div class="col-span-12 lg:col-span-4 space-y-4">
                                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-4">
                                    
                                    <!-- PRODUCTO -->
                                    <div class="space-y-2" x-data="{ open: false }">
                                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Perfil / Producto <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <button @click="open = !open; if(open) setTimeout(() => $refs.itemSearchInput.focus(), 100)" type="button" 
                                                    class="w-full flex items-center justify-between bg-gray-50 dark:bg-gray-900 border @error('selectedItemId') border-red-500 @else border-transparent @enderror rounded-xl text-sm p-3 focus:ring-2 focus:ring-indigo-500 dark:text-white transition-all shadow-sm">
                                                <span class="truncate">
                                                    @if($selectedItemId)
                                                        {{ $items->firstWhere('id', $selectedItemId)?->name }}
                                                        <span class="text-[10px] text-gray-500 block">({{ $items->firstWhere('id', $selectedItemId)?->internal_code }})</span>
                                                    @else
                                                        Seleccione un producto
                                                    @endif
                                                </span>
                                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>
                                            @error('selectedItemId') <span class="text-[10px] text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror

                                            <div x-show="open" @click.away="open = false" 
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden"
                                                wire:ignore.self>
                                                <div class="p-3 border-b border-gray-50 dark:border-gray-700">
                                                    <input x-ref="itemSearchInput" type="text" wire:model.live.debounce.300ms="itemSearch" placeholder="Buscar producto..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:text-white outline-none">
                                                </div>
                                                <div class="max-h-60 overflow-y-auto p-2 space-y-1">
                                                    @forelse($items as $item)
                                                        <button wire:key="item-{{ $item->id }}" type="button" @click="$wire.set('selectedItemId', '{{ $item->id }}'); open = false" class="w-full text-left px-4 py-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors group">
                                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-200 group-hover:text-indigo-600">{{ $item->name }}</div>
                                                            <div class="text-xs text-gray-500">{{ $item->internal_code }}</div>
                                                        </button>
                                                    @empty
                                                        <div class="p-4 text-center text-sm text-gray-500 italic">No se encontraron productos</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- CLIENTE -->
                                    <div class="space-y-2" x-data="{ open: false }">
                                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Cliente <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <button @click="open = !open; if(open) setTimeout(() => $refs.customerSearchInput.focus(), 100)" type="button" 
                                                    class="w-full flex items-center justify-between bg-gray-50 dark:bg-gray-900 border @error('customerId') border-red-500 @else border-transparent @enderror rounded-xl text-sm p-3 focus:ring-2 focus:ring-indigo-500 dark:text-white transition-all shadow-sm">
                                                <span class="truncate">
                                                    @if($customerId)
                                                        {{ $customers->firstWhere('id', $customerId)?->full_name }}
                                                    @else
                                                        Seleccione un cliente
                                                    @endif
                                                </span>
                                                <svg class="w-5 h-5 text-gray-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>
                                            @error('customerId') <span class="text-[10px] text-red-500 font-bold mt-1 block">{{ $message }}</span> @enderror

                                            <div x-show="open" @click.away="open = false" 
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden"
                                                wire:ignore.self>
                                                <div class="p-3 border-b border-gray-50 dark:border-gray-700">
                                                    <input x-ref="customerSearchInput" type="text" wire:model.live.debounce.300ms="customerSearch" placeholder="Buscar cliente..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:text-white outline-none">
                                                </div>
                                                <div class="max-h-60 overflow-y-auto p-2 space-y-1">
                                                    @forelse($customers as $customer)
                                                        <button wire:key="customer-{{ $customer->id }}" type="button" @click="$wire.set('customerId', '{{ $customer->id }}'); open = false" class="w-full text-left px-4 py-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors group">
                                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-200 group-hover:text-indigo-600">{{ $customer->full_name }}</div>
                                                        </button>
                                                    @empty
                                                        <div class="p-4 text-center text-sm text-gray-500 italic">No se encontraron clientes</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- REMISIÓN -->
                                    <div class="space-y-2" x-data="{ open: false }">
                                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider"># Remisión (OP)</label>
                                        <div class="relative">
                                            <button @click="open = !open; if(open) setTimeout(() => $refs.remSearchInput.focus(), 100)" type="button" class="w-full flex items-center justify-between bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-sm p-3 focus:ring-2 focus:ring-indigo-500 dark:text-white transition-all shadow-sm">
                                                <span class="truncate">
                                                    @if($remissionId)
                                                        RE #{{ $remissions->firstWhere('id', $remissionId)?->consecutive }}
                                                    @else
                                                        {{ $customerId ? 'Seleccione remisión' : 'Primero elija un cliente' }}
                                                    @endif
                                                </span>
                                                <svg class="w-5 h-5 text-gray-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>

                                            <div x-show="open && @json($customerId)" @click.away="open = false" 
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="opacity-0 scale-95"
                                                x-transition:enter-end="opacity-100 scale-100"
                                                class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden"
                                                wire:ignore.self>
                                                <div class="p-3 border-b border-gray-50 dark:border-gray-700">
                                                    <input x-ref="remSearchInput" type="text" wire:model.live.debounce.300ms="remissionSearch" placeholder="Buscar consecutivo..." class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 dark:text-white outline-none">
                                                </div>
                                                <div class="max-h-60 overflow-y-auto p-2 space-y-1">
                                                    <button type="button" @click="$wire.set('remissionId', ''); open = false" class="w-full text-left p-2 rounded-xl border border-dashed border-gray-200 text-[10px] text-gray-400 mb-1 hover:bg-gray-50">Sin Remisión</button>
                                                    @forelse($remissions as $rem)
                                                        <button wire:key="rem-{{ $rem->id }}" type="button" @click="$wire.set('remissionId', '{{ $rem->id }}'); open = false" class="w-full text-left px-4 py-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors group">
                                                            <div class="text-sm font-medium text-gray-700 dark:text-gray-200 group-hover:text-indigo-600">#{{ $rem->consecutive }}</div>
                                                            <div class="text-[10px] text-gray-500">{{ $rem->created_at->format('d/m/Y') }}</div>
                                                        </button>
                                                    @empty
                                                        <div class="p-4 text-center text-sm text-gray-500 italic">No hay remisiones</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1"># de Perfiles</label>
                                            <input type="number" wire:model="repeats" min="1" class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-sm p-2.5 focus:ring-2 focus:ring-indigo-500 dark:text-white">
                                            @error('repeats') <span class="text-[9px] text-red-500 font-bold block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Largo Perfil (mm)</label>
                                            <input type="number" wire:model.live="profileLength" class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-sm p-2.5 focus:ring-2 focus:ring-indigo-500 dark:text-white">
                                            @error('profileLength') <span class="text-[9px] text-red-500 font-bold block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div x-show="!remissionId" x-transition class="p-4 bg-red-50 dark:bg-red-900/20 rounded-2xl border-2 border-red-200 dark:border-red-800/30 animate-pulse-subtle">
                                        <label class="block text-[10px] font-black text-red-600 uppercase mb-2 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                            Justificación requerida *
                                        </label>
                                        <textarea x-model="justification" rows="3" 
                                                  class="w-full bg-white dark:bg-gray-900 border-2 @error('justification') border-red-500 ring-2 ring-red-500/20 @else border-red-200 dark:border-red-900/30 @enderror rounded-xl text-sm p-3 focus:ring-2 focus:ring-red-500 dark:text-white outline-none placeholder:text-red-300 font-medium" 
                                                  placeholder="Escriba aquí el motivo del corte personalizado..."></textarea>
                                        @error('justification') <span class="text-[10px] text-red-600 font-black block mt-2 text-center uppercase">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Main Dynamic Area -->
                            <div class="col-span-12 lg:col-span-8 flex flex-col gap-6">
                                
                                <!-- Visualizer Card -->
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-lg">
                                    <div class="flex justify-between items-end mb-4">
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Mapa de Corte del Perfil</h4>
                                            <p class="text-[11px] text-gray-400">Visualización en tiempo real (+5mm kerf)</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-2xl font-black text-gray-900 dark:text-white" x-text="(accumulated / 10).toFixed(1) + ' cm'"></span>
                                            <div class="text-[10px] font-bold text-gray-400 uppercase">Acumulado Total</div>
                                        </div>
                                    </div>

                                    <div class="relative w-full h-12 bg-gray-100 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden flex shadow-inner group">
                                        <template x-for="(cut, index) in cuts" :key="index">
                                            <div class="flex h-full transition-all duration-300" :style="'width: ' + (((parseFloat(cut) || 0) + kerf) / profileLength * 100) + '%'">
                                                <div class="bg-indigo-500 h-full border-r border-indigo-400/30 flex items-center justify-center overflow-hidden hover:bg-indigo-400"
                                                     :style="'width: ' + ((parseFloat(cut) || 0) / ((parseFloat(cut) || 0) + kerf) * 100) + '%'">
                                                    <span class="text-[8px] font-bold text-white px-0.5" x-text="cut" x-show="cut > 20"></span>
                                                </div>
                                                <div class="bg-amber-500/80 h-full" :style="'width: ' + (kerf / ((parseFloat(cut) || 0) + kerf) * 100) + '%'"></div>
                                            </div>
                                        </template>
                                        <div class="absolute inset-0 pointer-events-none border-r-2 border-red-500 border-dashed" x-show="accumulated > profileLength"></div>
                                    </div>

                                    <div class="mt-4 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 p-3 rounded-xl">
                                        <div class="flex gap-4">
                                            <div class="flex items-center gap-2"><div class="w-3 h-3 bg-indigo-500 rounded-sm"></div><span class="text-[10px] text-gray-500 font-bold uppercase">ÚTIL</span></div>
                                            <div class="flex items-center gap-2"><div class="w-3 h-3 bg-amber-500/80 rounded-sm"></div><span class="text-[10px] text-gray-500 font-bold uppercase">MERMA</span></div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold text-gray-400 uppercase">Sobrante:</span>
                                            <span class="text-sm font-black text-emerald-600" x-text="(remaining / 10).toFixed(1) + ' cm'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inputs Area -->
                                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex-1">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Configurar Segmentos de Corte</h4>
                                        <button wire:click="addCut" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl transition-all active:scale-95 shadow-md flex items-center gap-2 text-[10px] font-bold uppercase">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Agregar Corte
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-h-48 overflow-y-auto custom-scrollbar p-1">
                                        @foreach($cuts as $index => $cut)
                                            <div class="relative group" wire:key="cut-{{ $index }}">
                                                <label class="absolute -top-1 left-2 px-1 bg-white dark:bg-gray-800 text-[8px] font-black text-gray-400">CORTE #{{ $index + 1 }}</label>
                                                <div class="flex items-center">
                                                    <input type="number" wire:model.live.debounce.150ms="cuts.{{ $index }}" 
                                                           class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-xl text-sm p-3 focus:ring-2 focus:ring-indigo-500 dark:text-white outline-none transition-all shadow-inner"
                                                           placeholder="0">
                                                    <button wire:click="removeCut({{ $index }})" class="absolute -top-2 -right-2 p-1.5 bg-red-100 text-red-500 rounded-full opacity-0 group-hover:opacity-100 transition-all hover:bg-red-500 hover:text-white shadow-sm border border-white dark:border-gray-700 z-10" title="Eliminar corte">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('cuts') <span class="text-[10px] text-red-500 font-bold block mt-2 text-center">{{ $message }}</span> @enderror

                                    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700 space-y-4">
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase">Observación Interna / Notas (Opcional)</label>
                                        <input type="text" wire:model="observations" class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-xl text-sm p-3 focus:ring-2 focus:ring-indigo-500 dark:text-white shadow-inner" placeholder="Ej: Notas adicionales...">
                                        
                                        <div class="flex justify-end gap-3 pt-4">
                                            <button @click="isNew = false" class="px-6 py-2.5 text-xs font-bold text-gray-500 uppercase hover:bg-gray-50 rounded-xl transition-all">Cancelar</button>
                                            
                                            <button wire:click="save" 
                                                    wire:loading.attr="disabled"
                                                    class="px-8 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase shadow-xl transition-all active:scale-95 disabled:opacity-50 flex items-center gap-2">
                                                <span wire:loading.remove>Guardar Plan de Corte</span>
                                                <span wire:loading class="flex items-center gap-2">
                                                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                                    Procesando...
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer Area -->
                <div class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <div x-show="!isNew" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $cutDetails->links() }}
                    </div>
                    <div x-show="isNew" class="text-[10px] text-gray-400 italic">
                        * Todos los campos marcados son obligatorios.
                    </div>
                    <button @click="show = false" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white text-xs font-bold rounded-lg transition-colors uppercase">
                        Cerrar Ventana
                    </button>
                </div>
                </div>
            </div>
            @endif
        </div>
    </template>
</div>
