<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Archivos del Proyecto</h2>
        <div class="flex bg-gray-100 dark:bg-gray-900 p-1 rounded-lg text-2xs font-semibold">
            <button wire:click="$set('typeFilter', 'todos')"
                class="px-3 py-1.5 rounded-md transition-colors {{ $typeFilter === 'todos' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                Todos
            </button>
            <button wire:click="$set('typeFilter', 'imagenes')"
                class="px-3 py-1.5 rounded-md transition-colors {{ $typeFilter === 'imagenes' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                Imágenes
            </button>
            <button wire:click="$set('typeFilter', 'documentos')"
                class="px-3 py-1.5 rounded-md transition-colors {{ $typeFilter === 'documentos' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                Documentos
            </button>
        </div>
    </div>

    @if($files->isEmpty())
        <div class="text-center text-gray-400 dark:text-gray-500 py-12">
            <x-heroicon-o-photo class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" />
            <p class="text-sm">Aún no se han adjuntado archivos a este proyecto.</p>
            <p class="text-2xs mt-1">Los archivos que se adjunten desde el chat aparecerán aquí automáticamente.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($files as $file)
                <a href="{{ Storage::url($file->file_path) }}" target="_blank"
                    class="group block bg-gray-50 dark:bg-gray-850 rounded-lg border border-gray-100 dark:border-gray-750 overflow-hidden hover:border-indigo-400 dark:hover:border-indigo-500 transition-colors">
                    <div class="aspect-square bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                        @if($file->is_image)
                            <img src="{{ Storage::url($file->file_path) }}" alt="{{ $file->file_name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        @elseif(in_array($file->file_type, ['xls', 'xlsx']))
                            <x-heroicon-o-table-cells class="w-10 h-10 text-emerald-500" />
                        @elseif($file->file_type === 'pdf')
                            <x-heroicon-o-document-text class="w-10 h-10 text-red-500" />
                        @else
                            <x-heroicon-o-document class="w-10 h-10 text-indigo-500" />
                        @endif
                    </div>
                    <div class="p-2.5">
                        <p class="text-2xs font-semibold text-gray-800 dark:text-gray-200 truncate" title="{{ $file->file_name }}">{{ $file->file_name }}</p>
                        <p class="text-3xs text-gray-400 mt-0.5 truncate">{{ $file->user->name ?? 'Usuario' }} · {{ $file->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
