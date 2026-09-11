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
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($entry->attachments as $att)
                        @if($att->isImage())
                        <a href="{{ route('tenant.instructivos.attachment', $att->id) }}" target="_blank"
                            class="w-20 h-20 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 block">
                            <img src="{{ route('tenant.instructivos.attachment', $att->id) }}" class="w-full h-full object-cover" alt="{{ $att->file_name }}">
                        </a>
                        @else
                        <a href="{{ route('tenant.instructivos.attachment', $att->id) }}" target="_blank"
                            class="flex items-center gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-xs text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                            <x-heroicon-o-paper-clip class="w-3.5 h-3.5" />
                            {{ $att->file_name }}
                        </a>
                        @endif
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
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">
                {{ $editingEntryId ? 'Editar entrada' : 'Nueva entrada' }}
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título</label>
                    <input type="text" wire:model="entryTitle" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                    @error('entryTitle') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contenido</label>
                    <textarea wire:model="entryBody" rows="6" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm"></textarea>
                    @error('entryBody') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Imágenes / archivos</label>
                    <input type="file" wire:model="tempFiles" multiple class="w-full text-sm text-gray-600 dark:text-gray-300">
                    <div wire:loading wire:target="tempFiles" class="text-xs text-gray-400 mt-1">Subiendo...</div>
                    @error('tempFiles.*') <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror

                    @if(!empty($tempFiles))
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($tempFiles as $i => $f)
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs text-gray-600 dark:text-gray-300">
                            {{ $f->getClientOriginalName() }}
                            <button type="button" wire:click="removeTempFile({{ $i }})" class="text-gray-400 hover:text-red-600">&times;</button>
                        </span>
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
