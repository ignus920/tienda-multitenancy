<div class="p-6">
    {{-- Header con Estilo Premium --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight flex items-center">
                <span class="p-3 bg-indigo-600 rounded-2xl mr-4 shadow-lg shadow-indigo-200 dark:shadow-none">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </span>
                Gestión de Despachos
            </h1>
            <p class="mt-2 text-gray-500 dark:text-gray-400 font-medium">Control de salidas de mercancía y generación de guías.</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="$set('showHistory', true)" class="inline-flex items-center px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Historial de Guías
            </button>
            <button wire:click="resetWizard" class="inline-flex items-center px-5 py-2.5 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 rounded-xl text-sm font-bold hover:bg-red-100 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reiniciar
            </button>
        </div>
    </div>

    {{-- Contenido Principal --}}
    <div class="grid grid-cols-1 gap-8">
        
        {{-- PASO 1: Selección de Transportadora --}}
        @if($step == 'carrier_selection')
            <div class="text-center mb-8">
                <h2 class="text-xl font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest">Seleccione la Transportadora</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach(['Servientrega', 'Coordinadora', 'Cliente'] as $carrier)
                    <button wire:click="selectCarrier('{{ $carrier }}')" 
                        class="group relative bg-white dark:bg-gray-800 p-8 rounded-3xl border-2 border-transparent hover:border-indigo-500 transition-all shadow-xl hover:shadow-2xl overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                            <svg class="w-24 h-24 text-indigo-900" fill="currentColor" viewBox="0 0 24 24"><path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.85,9.48l2.03,1.58C4.84,11.36,4.81,11.69,4.81,12c0,0.31,0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.5c-1.93,0-3.5-1.57-3.5-3.5 s1.57-3.5,3.5-3.5s3.5,1.57,3.5,3.5S13.93,15.5,12,15.5z"/></svg>
                        </div>
                        <div class="flex flex-col items-center gap-6">
                            <div class="w-32 h-32 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center shadow-inner">
                                @if($carrier == 'Servientrega')
                                    <span class="text-4xl font-black text-green-600">S</span>
                                @elseif($carrier == 'Coordinadora')
                                    <span class="text-4xl font-black text-blue-800">C</span>
                                @else
                                    <span class="text-4xl font-black text-indigo-600">👤</span>
                                @endif
                            </div>
                            <span class="text-2xl font-bold text-gray-800 dark:text-white">{{ $carrier }}</span>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- PASO 2-4: Interfaz de Escaneo --}}
        @if($step != 'carrier_selection')
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="p-8 bg-indigo-600">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-md">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-white">{{ $selectedCarrier }}</h3>
                                <p class="text-indigo-100 font-medium">Modo de escaneo activo</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-4 py-1.5 bg-green-400 text-green-900 rounded-full text-xs font-black uppercase tracking-widest">En Línea</span>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    {{-- Banner de Estado del Escáner --}}
                    <div class="mb-8 p-6 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border-l-8 @if($inputFocused) border-green-500 @else border-red-500 @endif transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="relative">
                                    <div class="w-4 h-4 rounded-full @if($inputFocused) bg-green-500 animate-ping @else bg-red-500 @endif"></div>
                                    <div class="absolute inset-0 w-4 h-4 rounded-full @if($inputFocused) bg-green-500 @else bg-red-500 @endif"></div>
                                </div>
                                <span class="text-lg font-bold text-gray-700 dark:text-gray-200">
                                    {{ $statusMessage }}
                                </span>
                            </div>
                            @if($currentUser)
                                <div class="flex items-center gap-3 px-4 py-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                        {{ substr($currentUser->name, 0, 1) }}
                                    </div>
                                    <span class="font-bold text-gray-700 dark:text-gray-300">{{ $currentUser->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Input de Escaneo (Enfocado) --}}
                    <div class="relative group">
                        <input type="text" 
                            wire:model.live="scanInput"
                            wire:keydown.enter="processScan"
                            id="scanner-input"
                            autofocus
                            placeholder="Escanee aquí..."
                            @focus="$wire.set('inputFocused', true)"
                            @blur="$wire.set('inputFocused', false)"
                            class="w-full p-8 text-3xl font-mono text-center tracking-widest bg-gray-50 dark:bg-gray-900 border-4 border-dashed @if($inputFocused) border-green-400 ring-4 ring-green-100 dark:ring-green-900/20 @else border-gray-200 dark:border-gray-700 @endif rounded-3xl transition-all focus:outline-none"
                        >
                        <div class="absolute inset-y-0 right-0 pr-8 flex items-center pointer-events-none">
                            <svg class="w-12 h-12 @if($inputFocused) text-green-400 @else text-gray-300 @endif transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                    </div>

                    {{-- Tabla de Ítems Escaneados --}}
                    <div class="mt-12 overflow-hidden rounded-3xl border border-gray-100 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest"># Orden (OP)</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-widest"># Guía</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-widest">Paquetes</th>
                                    <th class="px-6 py-4 text-right text-xs font-black text-gray-500 uppercase tracking-widest">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($scannedItems as $index => $item)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-lg font-bold text-sm">OP-{{ $item['op'] }}</span>
                                        </td>
                                        <td class="px-6 py-4 font-mono text-gray-600 dark:text-gray-400 font-bold">{{ $item['guide'] }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full font-black text-gray-700 dark:text-gray-300">
                                                {{ $item['packages'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button wire:click="removeItem({{ $index }})" class="text-red-400 hover:text-red-600 transition-colors">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">No hay ítems escaneados aún.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Botón de Guardado Final --}}
                    @if(count($scannedItems) > 0)
                        <div class="mt-8 flex justify-end">
                            <button wire:click="saveBatch" class="px-10 py-4 bg-indigo-600 text-white rounded-2xl font-black text-lg hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-200 dark:shadow-none flex items-center gap-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Registrar Despachos
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- MODAL: Historial de Guías --}}
    @if($showHistory)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Historial de Guías</h3>
                    <button wire:click="$set('showHistory', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto">
                    <div class="flex gap-4 mb-6">
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Desde</label>
                            <input type="date" wire:model.live="dateFrom" class="w-full bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-sm">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Hasta</label>
                            <input type="date" wire:model.live="dateTo" class="w-full bg-gray-50 dark:bg-gray-700 border-0 rounded-xl text-sm">
                        </div>
                    </div>
                    <table class="min-w-full">
                        <thead>
                            <tr class="text-left text-xs font-bold text-gray-400 uppercase">
                                <th class="pb-4">Fecha</th>
                                <th class="pb-4">OP</th>
                                <th class="pb-4">Guía</th>
                                <th class="pb-4">Transportadora</th>
                                <th class="pb-4">Usuario</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                            @forelse($history as $record)
                                <tr>
                                    <td class="py-4 text-sm text-gray-600 dark:text-gray-400">{{ $record->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-4 font-bold text-gray-700 dark:text-gray-200">#{{ $record->production_order_id }}</td>
                                    <td class="py-4 font-mono text-indigo-500 font-bold underline cursor-pointer">{{ $record->guide_number }}</td>
                                    <td class="py-4">
                                        <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs font-bold text-gray-600 dark:text-gray-300">
                                            {{ $record->carrier }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-sm text-gray-500">{{ $record->user->name ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 italic">No hay registros para este periodo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Mantener el foco en el input del escáner
            const focusInput = () => {
                const input = document.getElementById('scanner-input');
                if (input) input.focus();
            };

            focusInput();
            setInterval(focusInput, 2000);

            // Escuchar eventos de éxito/error para SweetAlert
            Livewire.on('show-toast', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                Swal.fire({
                    icon: data.type,
                    title: data.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        });
    </script>
</div>
