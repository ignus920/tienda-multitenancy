<div x-data="{ 
        isOpen: @entangle('isOpen'),
        activeTab: 'accesorios'
     }" 
     x-show="isOpen" 
     x-on:keydown.escape.window="isOpen = false"
     class="fixed inset-0 z-[70] overflow-y-auto" 
     style="display: none;"
     x-cloak>
    
    <!-- Overlay -->
    <div class="fixed inset-0 transition-opacity" aria-hidden="true" x-on:click="isOpen = false">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
    </div>

    <!-- Modal Content -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden border border-gray-200 dark:border-slate-700 transform transition-all"
             x-show="isOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Header -->
            <div class="px-6 py-4 flex items-center justify-between border-b border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-700/30">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Accesorios Sugeridos</h3>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold">{{ $itemName }}</p>
                    </div>
                </div>
                <button x-on:click="isOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                <!-- Lista de asignados (Solo Lectura) -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h5 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Insumos a alistar</h5>
                        <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-[10px] font-bold rounded-md border border-indigo-100 dark:border-indigo-800">
                            {{ count($assignedAccesorios) }} {{ count($assignedAccesorios) == 1 ? 'Ítem' : 'Ítems' }}
                        </span>
                    </div>
                    
                    <div class="space-y-2 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                        @forelse($assignedAccesorios as $acc)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-slate-700/50 border border-gray-100 dark:border-slate-700 rounded-2xl group transition-all hover:border-indigo-200 dark:hover:border-indigo-900/50">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 bg-white dark:bg-slate-800 rounded-xl flex items-center justify-center text-indigo-500 shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                                        @php
                                            $insumo = \App\Models\Tenant\Items\Items::find($acc['insumo']['id']);
                                            $imageUrl = !empty($acc['image']) 
                                                ? \Illuminate\Support\Facades\Storage::url($acc['image']) 
                                                : ($insumo ? $insumo->getPrincipalThumbnailUrl('BODEGA') : asset('images/placeholder-item.png'));
                                        @endphp
                                        <img src="{{ $imageUrl }}" alt="Accesorio" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                                                {{ $acc['insumo']['name'] ?? 'Insumo no encontrado' }}
                                            </p>
                                            <span class="px-2 py-0.5 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-[10px] font-black rounded-md border border-red-200 dark:border-red-800">
                                                Cant: {{ $acc['quantity'] ?? 1 }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] font-mono bg-gray-200 dark:bg-slate-600 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300">
                                                {{ $acc['insumo']['internal_code'] ?? 'N/A' }}
                                            </span>
                                            @if(!empty($acc['observacion']))
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400 italic">
                                                    "{{ $acc['observacion'] }}"
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 bg-gray-50/50 dark:bg-slate-700/20 rounded-2xl border-2 border-dashed border-gray-200 dark:border-slate-700">
                                <svg class="w-12 h-12 text-gray-300 dark:text-slate-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Este producto no requiere accesorios.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/30 border-t border-gray-100 dark:border-slate-700 flex justify-end">
                <button x-on:click="isOpen = false" class="px-8 py-2.5 bg-gray-900 dark:bg-indigo-600 text-white text-sm font-bold rounded-xl hover:bg-gray-800 dark:hover:bg-indigo-700 transition-all shadow-lg active:scale-95">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>
