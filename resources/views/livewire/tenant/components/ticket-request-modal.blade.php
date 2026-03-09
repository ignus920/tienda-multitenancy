<div>
<template x-teleport="body">
<div x-data="{
    show: @entangle('isOpen'),
    quill: null,
    initQuill() {
        if (this.quill) return;
        this.quill = new Quill($refs.editor, {
            theme: 'snow',
            placeholder: 'Escribe aquí los detalles de tu solicitud...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'header': [1, 2, 3, false] }],
                    ['clean']
                ]
            }
        });
        this.quill.on('text-change', () => {
            $wire.set('detail', this.quill.root.innerHTML);
        });
    }
}"
x-show="show"
x-init="$watch('show', value => { if(value) { setTimeout(() => initQuill(), 100); } })"
x-cloak
style="display:none;"
class="fixed inset-0 z-[9999] flex items-center justify-center p-4">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>

    <!-- Modal Panel -->
    <div class="relative z-10 bg-white dark:bg-gray-800 rounded-xl w-full max-w-4xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col max-h-[90vh]"
         @click.stop>

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                    {{ $title }}
                    @if($productName)
                        <span class="text-indigo-500 dark:text-indigo-400 font-normal ml-2">— {{ $productName }}</span>
                    @endif
                </h3>
            </div>
            <button @click="show = false" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Detalle o Formulario -->
        @if($selectedRequest)
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Historial de la solicitud</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-80 overflow-y-auto custom-scrollbar p-1">
                    @foreach($selectedRequest->history as $history)
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm relative group transition-all hover:shadow-md">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-[13px] font-bold text-amber-500 dark:text-amber-400">
                                    {{ $history->user->name ?? 'Usuario Sistema' }}
                                </span>
                                <div class="flex items-center gap-2">
                                    @php
                                        $statusName = $history->status->name ?? 'Estado N/A';
                                        $statusColor = $history->status->color ?? 'gray';
                                        // Mapeo simple de colores comunes de DB a Tailwind HEX o clases si se prefiere
                                        // Pero para style, mejor HEX o nombres CSS válidos
                                        $colorMap = [
                                            'indigo' => '#4f46e5',
                                            'green' => '#16a34a',
                                            'blue' => '#2563eb',
                                            'red' => '#dc2626',
                                            'maroon' => '#800000',
                                        ];
                                        $finalColor = $colorMap[strtolower($statusColor)] ?? $statusColor;
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase text-white tracking-wider" 
                                          style="background-color: {{ $finalColor }}">
                                        {{ $statusName }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-[10px] text-gray-400 mb-3 flex items-center gap-1">
                                <x-heroicon-o-clock class="w-3 h-3"/>
                                {{ $history->created_at ? $history->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed bg-gray-50/50 dark:bg-gray-900/30 p-2 rounded-lg border border-gray-50 dark:border-gray-800">
                                @if(trim(strip_tags($history->message)) == 'Solicitud creada.')
                                    {!! $selectedRequest->detail !!}
                                @else
                                    {!! $history->message ?? 'Sin mensaje' !!}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Body -->
        <div class="p-6 space-y-5 overflow-y-auto custom-scrollbar flex-1">
            @if($isModuleActive)
                <!-- Selector de Departamento (Solo en modo Nuevo) -->
                @if(!$selectedRequest)
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Departamento <span class="text-red-500">*</span></label>
                        <select wire:model="department_id"
                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all dark:text-white">
                            <option value="">Selecciona una opción</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <span class="text-red-500 text-[10px] mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                @endif

                <!-- Detalle/Comentario (Siempre visible) -->
                <div wire:ignore>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">
                        {{ $selectedRequest ? 'Añadir Comentario al Historial' : 'Detalle de la Solicitud' }} <span class="text-red-500">*</span>
                    </label>
                    <div x-ref="editor" class="h-36 dark:text-white rounded-lg bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-600"></div>
                </div>
                @error('detail') <span class="text-red-500 text-[10px] block font-medium">{{ $message }}</span> @enderror

                <!-- Botones -->
                <div class="flex justify-end items-center gap-2 pt-1 flex-wrap">
                    @if($selectedRequest)
                        <!-- Botones de Estado (Modo Ver) -->
                    
                        <button wire:click="updateStatus('Reactivar')"
                            class="px-4 py-2 text-xs font-bold text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors flex items-center gap-1.5">
                            <x-heroicon-o-arrow-path class="w-3.5 h-3.5"/>
                            Reactivar
                        </button>
                        <button wire:click="updateStatus('Solucionado')"
                            class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors flex items-center gap-1.5">
                            <x-heroicon-o-check-circle class="w-3.5 h-3.5"/>
                            Solucionado
                        </button>
                        <button wire:click="updateStatus('Imposibilidad')"
                            class="px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors flex items-center gap-1.5">
                            <x-heroicon-o-x-circle class="w-3.5 h-3.5"/>
                            Imposibilidad
                        </button>
                    @else
                        <!-- Botones de Guardar (Modo Nuevo) -->
                        <button @click="show = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Cancelar
                        </button>
                        <button wire:click="save"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Guardar
                        </button>
                    @endif
                </div>

                <!-- Historial -->
                <div class="border-t border-gray-100 dark:border-gray-700 pt-5">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Historial de Solicitudes</p>

                    <!-- Filtros -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Desde</label>
                            <input type="date" wire:model.live="dateFrom" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-xs py-2 px-3 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Hasta</label>
                            <input type="date" wire:model.live="dateTo" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-xs py-2 px-3 dark:text-white">
                        </div>
                        <div class="col-span-2">
                            <input type="text" wire:model.live="search" placeholder="Buscar..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-xs py-2 px-3 dark:text-white">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-3 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center w-10">#</th>
                                    <th class="px-3 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                    <th class="px-3 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Depto.</th>
                                    <th class="px-3 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Detalle</th>
                                    <th class="px-3 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Estado</th>
                                    <th class="px-3 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Ver</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($requests as $req)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <td class="px-3 py-3 font-medium text-gray-400 text-center">{{ $req->id }}</td>
                                    <td class="px-3 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ $req->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            {{ $req->department->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-gray-700 dark:text-gray-200 max-w-[180px] truncate">
                                        {!! Str::words(strip_tags($req->detail), 8) !!}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase text-white"
                                            style="background-color: #3b82f6">
                                            {{ $req->status->name }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <button wire:click="view({{ $req->id }})" class="p-1 bg-green-500 text-white rounded hover:bg-green-600 transition-colors">
                                            <x-heroicon-o-eye class="w-3.5 h-3.5"/>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-8 text-center text-gray-400 italic text-xs uppercase tracking-wider">No se encontraron entradas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Paginacion -->
                @if(method_exists($requests, 'hasPages') && $requests->hasPages())
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 rounded-b-xl flex-shrink-0">
                        {{ $requests->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .ql-toolbar.ql-snow { border-color: #e5e7eb !important; background: #f9fafb; border-radius: 8px 8px 0 0; }
    .dark .ql-toolbar.ql-snow { background: #1e293b; border-color: #374151 !important; }
    .ql-container.ql-snow { border-color: #e5e7eb !important; border-radius: 0 0 8px 8px; font-size: 13px; }
    .dark .ql-container.ql-snow { background: #111827; border-color: #374151 !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; }
</style>
</template>
</div>
