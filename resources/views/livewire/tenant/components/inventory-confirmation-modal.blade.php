<div>
<template x-teleport="body">
<div x-data="{
    show: @entangle('isOpen').live
}"
x-show="show"
x-cloak
style="display:none;"
class="fixed inset-0 z-[9999] flex items-center justify-center p-4">

    @if($isOpen)
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>

    <!-- Modal Panel -->
    <div class="relative z-10 bg-white dark:bg-gray-800 rounded-xl w-full max-w-4xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col max-h-[90vh]"
         @click.stop>

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                    Confirmación de Inventario
                    @if($product)
                        <span class="text-blue-500 dark:text-blue-400 font-normal ml-2">
                            — @if($product->internal_code){{ $product->internal_code }} - @endif{{ $product->name }}
                        </span>
                    @endif
                </h3>
            </div>
            <button @click="show = false" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Formulario -->
                <div class="space-y-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Nueva Solicitud</p>
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Cantidad Solicitada <span class="text-red-500">*</span></label>
                        <input wire:model="requested_quantity"
                            type="number"
                            min="1"
                            placeholder="Ej: 10"
                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all dark:text-white">
                        @error('requested_quantity') <span class="text-red-500 text-[10px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Observaciones / Motivo</label>
                        <textarea wire:model="observations"
                            rows="3"
                            placeholder="Ej: Verificación para cliente mayorista..."
                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all dark:text-white"></textarea>
                        @error('observations') <span class="text-red-500 text-[10px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end">
                        <button wire:click="save"
                            wire:loading.attr="disabled"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-lg shadow-blue-600/20 transition-all active:scale-95 flex items-center gap-2">
                            <span wire:loading wire:target="save" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                            Enviar Solicitud
                        </button>
                    </div>
                </div>

                <!-- Info de Stock Actual -->
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl p-5 border border-gray-100 dark:border-gray-700/50 h-fit">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Stock en Sistema</p>
                    
                    <div class="grid grid-cols-2 gap-4">
                        @if($product && $product->invItemsStore)
                            @foreach($product->invItemsStore as $store)
                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                                    <p class="text-[10px] text-gray-500 uppercase font-bold truncate">{{ $store->store->name ?? 'Bodega' }}</p>
                                    <p class="text-lg font-black text-gray-900 dark:text-white">{{ number_format($store->stock_items_store, 0) }}</p>
                                </div>
                            @endforeach
                            
                            <div class="col-span-2 mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                <span class="text-xs font-bold text-gray-500">TOTAL GLOBAL</span>
                                <span class="text-lg font-black text-blue-600 dark:text-blue-400">
                                    {{ number_format($product->invItemsStore->sum('stock_items_store'), 0) }}
                                </span>
                            </div>
                        @else
                            <div class="col-span-2 text-center py-4 text-gray-400 italic text-xs">
                                No hay información de stock disponible
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Historial -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Historial de Solicitudes para este Producto</p>

                <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/80 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3 font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-3 font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Solicitante</th>
                                <th class="px-4 py-3 font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Cant. Sol.</th>
                                <th class="px-4 py-3 font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Estado</th>
                                <th class="px-4 py-3 font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Resultado / Confirmador</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @forelse($confirmations as $conf)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        {{ $conf->requested_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $conf->requester->name ?? 'Sistema' }}</span>
                                        @if($conf->observations)
                                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $conf->observations }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-black text-gray-700 dark:text-gray-300">
                                        {{ number_format($conf->requested_quantity, 0) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($conf->status == 1)
                                            <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-[10px] font-bold uppercase tracking-wider border border-amber-200 dark:border-amber-800">
                                                Pendiente
                                            </span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-[10px] font-bold uppercase tracking-wider border border-green-200 dark:border-green-800">
                                                Confirmado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($conf->status == 2)
                                            <div class="flex items-center gap-2">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-green-600 dark:text-green-400">{{ number_format($conf->confirmed_quantity, 0) }} confirmados</span>
                                                    <span class="text-[10px] text-gray-400">Por: {{ $conf->confirmer->name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic text-[10px]">Esperando confirmación...</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 italic text-xs uppercase tracking-wider">
                                        No hay historial de confirmaciones para este ítem
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($confirmations instanceof \Illuminate\Pagination\LengthAwarePaginator && $confirmations->hasPages())
                    <div class="mt-4">
                        {{ $confirmations->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl flex justify-end">
            <button @click="show = false" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                Cerrar ventana
            </button>
        </div>
    </div>
    @endif

</div>
</template>

<style>
    [x-cloak] { display: none !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; }
</style>
</div>
