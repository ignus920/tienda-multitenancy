<div>
    <!-- Modal de Historial -->
    <div x-data="{ show: @entangle('showModal') }"
         x-show="show"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="$wire.closeModal()">
        
        <!-- Overlay -->
        <div x-show="show"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity"
             @click="$wire.closeModal()">
        </div>

        <!-- Modal Content -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-2xl transition-all w-full max-w-4xl"
                 @click.stop>
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">Historial del Item</h3>
                                @if($itemSku)
                                    <p class="text-sm text-white/80">{{ $itemSku }} - {{ $itemName }}</p>
                                @endif
                            </div>
                        </div>
                        <button @click="$wire.closeModal()" 
                                class="p-2 hover:bg-white/20 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 max-h-[60vh] overflow-y-auto">
                    @if($loading)
                        <!-- Loading State -->
                        <div class="flex items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-amber-500"></div>
                        </div>
                    @elseif(count($history) === 0)
                        <!-- Empty State -->
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No hay registros de historial</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center">No hay registros de historial para este item.</p>
                        </div>
                    @else
                        <!-- Desktop Table View -->
                        <div class="hidden lg:block">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Etiqueta</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cant. Solicitada</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cant. Enviada</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Precio</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($history as $record)
                                        @php $status = $this->getStatusLabel($record['status']); @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300">
                                                    {{ $record['label_name'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">
                                                {{ number_format($record['qty_requested'] ?? 0) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                {{ number_format($record['qty_shipped'] ?? 0) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                ${{ number_format($record['price'] ?? 0, 2) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                @switch($status['color'])
                                                    @case('yellow')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">{{ $status['label'] }}</span>
                                                        @break
                                                    @case('blue')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">{{ $status['label'] }}</span>
                                                        @break
                                                    @case('indigo')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300">{{ $status['label'] }}</span>
                                                        @break
                                                    @case('purple')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300">{{ $status['label'] }}</span>
                                                        @break
                                                    @case('cyan')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 dark:bg-cyan-900/30 text-cyan-800 dark:text-cyan-300">{{ $status['label'] }}</span>
                                                        @break
                                                    @case('green')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">{{ $status['label'] }}</span>
                                                        @break
                                                    @case('emerald')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300">{{ $status['label'] }}</span>
                                                        @break
                                                    @case('red')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">{{ $status['label'] }}</span>
                                                        @break
                                                    @default
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300">{{ $status['label'] }}</span>
                                                @endswitch
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $record['created_at'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="lg:hidden space-y-4">
                            @foreach($history as $record)
                                @php $status = $this->getStatusLabel($record['status']); @endphp
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300">
                                            {{ $record['label_name'] }}
                                        </span>
                                        @switch($status['color'])
                                            @case('yellow')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">{{ $status['label'] }}</span>
                                                @break
                                            @case('blue')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">{{ $status['label'] }}</span>
                                                @break
                                            @case('indigo')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300">{{ $status['label'] }}</span>
                                                @break
                                            @case('purple')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300">{{ $status['label'] }}</span>
                                                @break
                                            @case('cyan')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-cyan-100 dark:bg-cyan-900/30 text-cyan-800 dark:text-cyan-300">{{ $status['label'] }}</span>
                                                @break
                                            @case('green')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">{{ $status['label'] }}</span>
                                                @break
                                            @case('emerald')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-300">{{ $status['label'] }}</span>
                                                @break
                                            @case('red')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">{{ $status['label'] }}</span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300">{{ $status['label'] }}</span>
                                        @endswitch
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Cant. Solicitada</p>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($record['qty_requested'] ?? 0) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Cant. Enviada</p>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($record['qty_shipped'] ?? 0) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Precio</p>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($record['price'] ?? 0, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Fecha</p>
                                            <p class="text-sm text-gray-900 dark:text-white">{{ $record['created_at'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 flex justify-end">
                    <button @click="$wire.closeModal()"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors font-medium">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
