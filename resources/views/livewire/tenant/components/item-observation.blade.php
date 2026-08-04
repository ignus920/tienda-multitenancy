<div x-data="{ isOpen: @entangle('isOpen').live, activeTab: 'tech' }" 
     x-show="isOpen" 
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     x-cloak>
    
    @if($isOpen)
    <div @click.away="isOpen = false" 
          class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition-all border border-gray-200 dark:border-gray-700">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 dark:bg-indigo-900/40 rounded-lg text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 2 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white leading-tight">
                        Observaciones
                    </h3>
                    <p class="text-xs text-indigo-500 font-semibold uppercase tracking-wider">
                        {{ $productCode }} — {{ $productName }}
                    </p>
                </div>
            </div>
            <button @click="isOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Pestañas (Tabs) estilo Folder -->
        <div class="px-6 border-b border-gray-200 dark:border-gray-700 flex gap-4 bg-gray-50/50 dark:bg-gray-900/30">
            <button @click="activeTab = 'tech'"
                    type="button"
                    class="py-3 px-1 text-sm font-semibold border-b-2 transition-all duration-200 outline-none"
                    :class="activeTab === 'tech' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'">
                Características Técnicas
            </button>
            @if(Auth::user()?->profile_id != 18)
            <button @click="activeTab = 'internal'"
                    type="button"
                    class="py-3 px-1 text-sm font-semibold border-b-2 transition-all duration-200 outline-none"
                    :class="activeTab === 'internal' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'">
                Observaciones Internas
            </button>
            @endif
            @if(Auth::user()?->profile_id != 18)
            <button @click="activeTab = 'commercial'"
                    type="button"
                    class="py-3 px-1 text-sm font-semibold border-b-2 transition-all duration-200 outline-none"
                    :class="activeTab === 'commercial' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'">
                Observaciones Comerciales
            </button>
            @endif
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6 max-h-[60vh] overflow-y-auto custom-scrollbar">
            <form wire:submit.prevent="save" id="observationForm" class="space-y-6">
                
                <!-- Características Técnicas -->
                <div x-show="activeTab === 'tech'" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="bg-emerald-50/30 dark:bg-emerald-900/10 rounded-xl p-4 border border-emerald-100 dark:border-emerald-800">
                    <textarea wire:model="technical_specifications"
                        {{ Auth::user()?->profile_id == 18 ? 'readonly' : '' }}
                        class="block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors placeholder:text-gray-400"
                        rows="8" placeholder="Detalla las especificaciones técnicas..."></textarea>
                </div>

                <!-- Observaciones Internas -->
                @if(Auth::user()?->profile_id != 18)
                <div x-show="activeTab === 'internal'" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="bg-indigo-50/30 dark:bg-indigo-900/10 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800">
                    <textarea wire:model="observations"
                        class="block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors placeholder:text-gray-400"
                        rows="8" placeholder="Escribe aquí las observaciones internas..."></textarea>
                </div>
                @endif

                <!-- Observaciones Comerciales -->
                @if(Auth::user()?->profile_id != 18)
                <div x-show="activeTab === 'commercial'" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="bg-amber-50/30 dark:bg-amber-900/10 rounded-xl p-4 border border-amber-100 dark:border-amber-800">
                    <textarea wire:model="commercial_observations"
                        class="block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors placeholder:text-gray-400"
                        rows="8" placeholder="Notas sobre decisiones comerciales..."></textarea>
                </div>
                @endif
            </form>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end gap-3">
            @if(Auth::user()?->profile_id == 18)
                <button @click="isOpen = false" type="button"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg transition-all active:scale-95">
                    Cerrar
                </button>
            @else
                <button @click="isOpen = false" type="button"
                    class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-bold rounded-xl transition-all shadow-sm">
                    Cancelar
                </button>
                <button form="observationForm" type="submit"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 dark:shadow-none transition-all active:scale-95 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Guardar
                </button>
            @endif
        </div>
    </div>
    @endif

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.5); }
    </style>
</div>>
</div>

