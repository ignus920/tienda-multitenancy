<div class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('tenant.instructivos.department', $instructivo->department_id) }}" wire:navigate class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                    <x-heroicon-o-arrow-left class="w-5 h-5" />
                </a>
                <div>
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $instructivo->title }}</h1>
                    <p class="text-gray-600 dark:text-slate-400 text-sm mt-1">{{ $instructivo->department->name }}</p>
                </div>
            </div>
            @if($canManage)
            <button wire:click="openNewEntry" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <x-heroicon-o-plus class="w-4 h-4" />
                Nueva entrada
            </button>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 border border-gray-200 dark:border-slate-700 transition-colors">
        @if($entries->isEmpty())
            <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                <p>Todavía no hay entradas en este instructivo.</p>
            </div>
        @else
        <div class="space-y-4">
            @foreach($entries as $entry)
            <div class="relative group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $entry->title }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $entry->author->name ?? '—' }} · {{ $entry->created_at->format('d/m/Y H:i') }}
                            @if($entry->updated_by) · editado @endif
                        </p>
                    </div>
                    @if($canManage)
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="openEditEntry({{ $entry->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30" title="Editar">
                            <x-heroicon-o-pencil class="w-4 h-4" />
                        </button>
                        <button wire:click="
                            Swal.fire({
                                title: '¿Desactivar entrada?',
                                text: 'Dejará de verse en la bitácora. No se elimina la información.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#ef4444',
                                cancelButtonColor: '#4f46e5',
                                confirmButtonText: 'Sí, desactivar',
                                cancelButtonText: 'Cancelar',
                                background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                                color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $wire.deactivateEntry({{ $entry->id }})
                                }
                            })
                        " class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30" title="Desactivar">
                            <x-heroicon-o-trash class="w-4 h-4" />
                        </button>
                    </div>
                    @endif
                </div>

                <p class="mt-3 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $entry->body }}</p>

                @if($entry->attachments->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-3">
                    @foreach($entry->attachments as $att)
                        <a href="{{ route('tenant.instructivos.attachment', $att->id) }}" target="_blank"
                            class="w-24 group/att" title="{{ $att->file_name }}">
                            <div class="w-24 h-20 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex items-center justify-center hover:ring-2 hover:ring-indigo-500 transition-all">
                                @if($att->isImage())
                                    <img src="{{ route('tenant.instructivos.attachment', $att->id) }}" class="w-full h-full object-cover" alt="{{ $att->file_name }}">
                                @elseif($att->isPdf())
                                    <div class="flex flex-col items-center justify-center p-2 bg-red-50 text-red-600 w-full h-full">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        <span class="text-[9px] font-bold mt-1">PDF</span>
                                    </div>
                                @elseif($att->isExcel())
                                    <div class="flex flex-col items-center justify-center p-2 bg-green-50 text-green-600 w-full h-full">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span class="text-[9px] font-bold mt-1">EXCEL</span>
                                    </div>
                                @else
                                    <x-heroicon-o-paper-clip class="w-6 h-6 text-gray-400" />
                                @endif
                            </div>
                            <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400 truncate group-hover/att:text-indigo-600">{{ $att->file_name }}</p>
                        </a>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Modal nueva/editar entrada --}}
    @if($showEntryModal)
    <div wire:key="entry-modal" x-data x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                {{ $editingEntryId ? 'Editar entrada' : 'Nueva entrada' }}
                <x-field-hint text="Cada entrada es como una página de la bitácora: un tema o procedimiento explicado paso a paso." />
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center">
                        Título
                        <x-field-hint text='El nombre corto de este tema. Ejemplo: "Cómo hacer el corte de caja diario".' />
                    </label>
                    <input type="text" wire:model="entryTitle" placeholder="Ej: Cómo hacer el corte de caja diario" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                    @error('entryTitle') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center">
                        Contenido
                        <x-field-hint text='La explicación completa, paso a paso. Ejemplo: "1. Contar el efectivo. 2. Comparar contra el sistema. 3. Registrar la diferencia si hay. 4. Firmar el cierre."' />
                    </label>
                    <textarea wire:model="entryBody" rows="6" placeholder="Ej: 1. Contar el efectivo de la caja.&#10;2. Comparar contra el sistema.&#10;3. Registrar la diferencia si hay.&#10;4. Firmar el cierre." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm"></textarea>
                    @error('entryBody') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center">
                        Imágenes / archivos (Excel, PDF o imágenes)
                        <x-field-hint text="Opcional. Adjunta fotos, un PDF o un Excel de apoyo para esta entrada. Puedes elegir varios archivos, incluso de carpetas distintas, uno por uno." />
                    </label>

                    <div x-data="{ isDropping: false }"
                         x-on:dragover.prevent="isDropping = true"
                         x-on:dragleave.prevent="isDropping = false"
                         x-on:drop.prevent="isDropping = false; if($event.dataTransfer.files.length) { @this.uploadMultiple('tempFiles', $event.dataTransfer.files) }"
                         :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30': isDropping, 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900': !isDropping }"
                         class="flex flex-col items-center justify-center w-full h-24 px-4 py-4 border-2 border-dashed rounded-lg cursor-pointer transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 relative">

                        <div class="flex flex-col items-center justify-center pointer-events-none">
                            <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold text-indigo-600">Haz clic</span> o arrastra archivos aquí</p>
                        </div>
                        <input type="file" wire:model="tempFiles" multiple accept=".png,.jpg,.jpeg,.webp,.pdf,.xlsx,.xls" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                    </div>

                    <div wire:loading wire:target="tempFiles" class="mt-2 text-xs text-indigo-600 font-semibold flex items-center gap-2">
                        <svg class="animate-spin h-3 w-3 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Subiendo...
                    </div>
                    @error('stagedFiles.*') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror

                    @if(!empty($stagedFiles))
                    <div class="mt-3 grid grid-cols-4 sm:grid-cols-6 gap-2 p-2 bg-gray-100 dark:bg-gray-900 rounded-lg">
                        @foreach($stagedFiles as $i => $f)
                        <div class="relative group aspect-square rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-800">
                            @php $ext = strtolower($f->getClientOriginalExtension()); @endphp
                            @if($ext === 'pdf')
                                <div class="flex flex-col items-center justify-center p-2 bg-red-50 text-red-600 w-full h-full">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    <span class="text-[9px] font-bold mt-1 truncate w-full text-center">PDF</span>
                                </div>
                            @elseif(in_array($ext, ['xls', 'xlsx']))
                                <div class="flex flex-col items-center justify-center p-2 bg-green-50 text-green-600 w-full h-full">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <span class="text-[9px] font-bold mt-1 truncate w-full text-center">EXCEL</span>
                                </div>
                            @else
                                <img src="{{ $f->temporaryUrl() }}" class="object-cover w-full h-full">
                            @endif
                            <button type="button" wire:click="removeTempFile({{ $i }})" class="absolute top-0 right-0 bg-red-500 text-white p-0.5 m-0.5 rounded opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-opacity">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showEntryModal', false)" type="button" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Cancelar</button>
                <button wire:click="saveEntry" type="button" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Guardar</button>
            </div>
        </div>
    </div>
    @endif
</div>
