<div>
<template x-teleport="body">
<div x-data="{
    show: @entangle('isOpen').live,
    quill: null,
    initQuill() {
        if (!this.$refs.editor) return;
        
        // Si ya hay una instancia, la destruimos para evitar duplicados o referencias muertas
        this.quill = null;
        
        this.quill = new Quill(this.$refs.editor, {
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

        // Establecer contenido inicial desde Livewire
        const initialContent = $wire.get('detail') || '';
        this.quill.root.innerHTML = initialContent;

        this.quill.on('text-change', () => {
            $wire.set('detail', this.quill.root.innerHTML);
        });

        // Observar cambios del servidor para limpiar o actualizar el editor Quill
        this.$watch('$wire.detail', value => {
            if (this.quill) {
                const cleanValue = value || '';
                // Si el servidor lo limpió, vaciamos el editor Quill
                if (cleanValue === '') {
                    this.quill.root.innerHTML = '';
                } else if (this.quill.root.innerHTML !== cleanValue) {
                    // Solo actualizar si realmente difiere para evitar bucles infinitos
                    this.quill.root.innerHTML = cleanValue;
                }
            }
        });
    }
}"
x-show="show"
x-init="$watch('show', value => { 
    if(value) { 
        setTimeout(() => initQuill(), 100); 
    } else {
        if(quill) quill = null;
    }
})"
x-cloak
style="display:none;"
class="fixed inset-0 z-[9999] flex items-center justify-center p-4">

    @if($isOpen)
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>

    <!-- Modal Panel -->
    <div class="relative z-10 bg-white dark:bg-gray-800 rounded-xl w-full max-w-5xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col max-h-[90vh]"
         @click.stop>

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/40 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center flex-wrap gap-2">
                    <span>{{ $title }}</span>
                    @if($selectedRequest)
                        @php
                            $statusColor = $selectedRequest->status->color ?? 'gray';
                            $colorMap = [
                                'indigo' => '#4f46e5',
                                'green' => '#16a34a',
                                'blue' => '#2563eb',
                                'red' => '#dc2626',
                                'maroon' => '#800000',
                            ];
                            $badgeColor = $colorMap[strtolower($statusColor)] ?? $statusColor;
                        @endphp
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase text-white tracking-wider" 
                              style="background-color: {{ $badgeColor }}">
                            {{ $selectedRequest->status->name }}
                        </span>
                    @endif
                    @if($productName)
                        <span class="text-indigo-500 dark:text-indigo-400 font-normal ml-2">
                            — @if($productCode){{ $productCode }} - @endif{{ $productName }}
                        </span>
                        @if($selectedRequest && $selectedRequest->product)
                            @php
                                $principal = $selectedRequest->product->principalImage ?? $selectedRequest->product->principalBodegaImage;
                                $imgUrl = $principal ? $principal->getImageUrl() : null;
                            @endphp
                            @if($imgUrl)
                                <a href="{{ $imgUrl }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 ml-3 px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Click acá para ver imagen principal
                                </a>
                            @endif
                        @endif
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
        <!-- Detalle o Formulario -->
        @if(!$selectedRequest && $canCreateSupplierRequest)
            <!-- Pestañas (Tabs) para tipo de solicitud -->
            <div class="px-6 pt-3 border-b border-gray-150 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex gap-4 flex-shrink-0">
                <button wire:click="$set('activeTab', 'internal')"
                    class="pb-2.5 text-xs font-bold uppercase tracking-wider border-b-2 transition-all {{ $activeTab === 'internal' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                    Solicitud Interna
                </button>
                <button wire:click="$set('activeTab', 'supplier')"
                    class="pb-2.5 text-xs font-bold uppercase tracking-wider border-b-2 transition-all {{ $activeTab === 'supplier' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                    Solicitud a Proveedor
                </button>
            </div>
        @endif

        @if($selectedRequest)
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-col flex-shrink-0">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">
                    Historial de la solicitud — Solicitado por: <span class="text-indigo-600 dark:text-indigo-400">{{ $selectedRequest->creator->name ?? 'Usuario Sistema' }}</span>
                </p>
                
                <div x-init="
                        const scroll = () => { $el.scrollTop = $el.scrollHeight };
                        $nextTick(() => scroll());
                        setTimeout(scroll, 50);
                        setTimeout(scroll, 200);
                        setTimeout(scroll, 500);
                        $watch('show', value => { if (value) setTimeout(scroll, 100) });
                        $watch('$wire.selectedRequestId', value => { if (value) setTimeout(scroll, 150) });
                        const observer = new MutationObserver(scroll);
                        observer.observe($el, { childList: true, subtree: true });
                     "
                     class="flex flex-col gap-4 overflow-y-auto custom-scrollbar pr-2 py-1"
                     style="max-height: 280px;">
                    @foreach($selectedRequest->history as $history)
                        @php
                            $isMe = $history->user_id === auth()->id();
                            $statusName = $history->status->name ?? 'Estado N/A';
                            $statusColor = $history->status->color ?? 'gray';
                            $colorMap = [
                                'indigo' => '#4f46e5',
                                'green' => '#16a34a',
                                'blue' => '#2563eb',
                                'red' => '#dc2626',
                                'maroon' => '#800000',
                            ];
                            $finalColor = $colorMap[strtolower($statusColor)] ?? $statusColor;

                            $commentText = $history->message;
                            if (trim(strip_tags($commentText)) == 'Solicitud creada.') {
                                $commentText = $selectedRequest->detail;
                            }
                            
                            $original = $commentText;
                            $translation = null;

                            if (strpos($commentText, '[TRANSLATED]') !== false) {
                                $parts = explode('[TRANSLATED]', $commentText);
                                $original = $parts[0];
                                $translation = $parts[1];
                            } elseif (strpos($commentText, '--- Translated to English ---') !== false) {
                                $parts = explode('--- Translated to English ---', $commentText);
                                $original = $parts[0];
                                $translation = $parts[1];
                            } elseif (strpos($commentText, '--- Traducido al Español ---') !== false) {
                                $parts = explode('--- Traducido al Español ---', $commentText);
                                $original = $parts[0];
                                $translation = $parts[1];
                            }
                        @endphp
                        
                        <div class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-900/20 text-blue-500 rounded-full flex items-center justify-center flex-shrink-0 border border-blue-100 dark:border-blue-900/50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <div class="w-0.5 h-full bg-gray-200 dark:bg-gray-700 my-1"></div>
                            </div>
                            
                            <div class="flex-1 bg-white dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-xl p-3 shadow-sm">
                                <div class="flex items-center justify-between gap-2 mb-1 flex-wrap">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-gray-900 dark:text-white text-xs">
                                            {{ $history->user->name ?? 'Usuario Sistema' }}
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase text-white tracking-wider" 
                                              style="background-color: {{ $finalColor }}">
                                            {{ $statusName }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $history->created_at ? $history->created_at->format('d/m/Y H:i') : 'N/A' }}
                                    </span>
                                </div>
                                <div class="w-full text-left" style="text-align: left !important;">
                                    <div class="text-xs text-gray-700 dark:text-gray-300 leading-normal text-left" style="text-align: left !important; margin: 0; padding: 0;">
                                        {!! trim($original) !!}
                                    </div>
                                    @if($translation)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 leading-normal italic text-left mt-1 border-t border-gray-100 dark:border-gray-600/50 pt-1" style="text-align: left !important; margin-top: 0.25rem !important; margin-bottom: 0; padding-top: 0.25rem !important;">
                                            {!! trim($translation) !!}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Body -->
        <div class="p-6 space-y-5 overflow-y-auto custom-scrollbar flex-1">
            @if($isModuleActive)
                <!-- Formulario de Creación -->
                @if(!$selectedRequest)
                    @if($activeTab === 'supplier')
                        <!-- Selector de Proveedor -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Proveedor <span class="text-red-500">*</span></label>
                            <select wire:model="supplier_id" {{ $hasDefaultSupplier ? 'disabled' : '' }}
                                class="w-full {{ $hasDefaultSupplier ? 'bg-gray-100 cursor-not-allowed dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-900' }} border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all dark:text-white">
                                <option value="">Selecciona un proveedor</option>
                                @foreach($this->suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                            @if($hasDefaultSupplier)
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 block">El proveedor está asignado de forma fija según la configuración del producto.</span>
                            @endif
                            @error('supplier_id') <span class="text-red-500 text-[10px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <!-- Selector de Departamento -->
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
                @endif

                <!-- Detalle/Comentario (Siempre visible) -->
                <div wire:ignore class="mt-2">
                    <div x-ref="editor" class="min-h-[120px] dark:text-white rounded-lg bg-gray-50 dark:bg-gray-900 border-gray-300 dark:border-gray-600" style="resize: vertical; overflow: auto;"></div>
                </div>
                @error('detail') <span class="text-red-500 text-[10px] block font-medium">{{ $message }}</span> @enderror

                <!-- Botones -->
                <div class="flex justify-end items-center gap-2 pt-1 flex-wrap">
                    @if($selectedRequest)
                        <button wire:click="updateStatus('Reactivado')"
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

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4 items-end">
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Desde</label>
                            <input type="date" wire:model.live="dateFrom" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-xs py-2 px-3 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">Hasta</label>
                            <input type="date" wire:model.live="dateTo" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-xs py-2 px-3 dark:text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-medium text-gray-400 uppercase mb-1">&nbsp;</label>
                            <input type="text" wire:model.live="search" placeholder="Buscar..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg text-xs py-2 px-3 dark:text-white">
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                                <tr>
                                    <th class="px-3 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center w-10">#</th>
                                    <th class="px-3 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                                    <th class="px-3 py-3 font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Depto. / Prov.
                                    </th>
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
                                            @if($req->supplier_id)
                                                {{ $req->supplier->name ?? 'Proveedor N/A' }}
                                            @else
                                                {{ $req->department->name ?? 'Depto N/A' }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-gray-700 dark:text-gray-200 max-w-[180px] truncate">
                                        {!! Str::words(strip_tags($req->detail), 8) !!}
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        @php
                                            $statusColor = $req->status->color ?? 'gray';
                                            $colorMap = [
                                                'indigo' => '#4f46e5',
                                                'green' => '#16a34a',
                                                'blue' => '#2563eb',
                                                'red' => '#dc2626',
                                                'maroon' => '#800000',
                                            ];
                                            $badgeColor = $colorMap[strtolower($statusColor)] ?? $statusColor;
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase text-white shadow-sm"
                                            style="background-color: {{ $badgeColor }}">
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

                @if($requests instanceof \Illuminate\Pagination\LengthAwarePaginator && $requests->hasPages())
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 rounded-b-xl flex-shrink-0">
                        {{ $requests->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
    @endif

</div>
</template>

<style>
    [x-cloak] { display: none !important; }
    .ql-toolbar.ql-snow { border-color: #e5e7eb !important; background: #f9fafb; border-radius: 8px 8px 0 0; }
    .dark .ql-toolbar.ql-snow { background: #1e293b; border-color: #374151 !important; }
    .ql-container.ql-snow { border-color: #e5e7eb !important; border-radius: 0 0 8px 8px; font-size: 13px; resize: vertical; overflow-y: auto; }
    .dark .ql-container.ql-snow { background: #111827; border-color: #374151 !important; }
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; }
</style>
</div>
