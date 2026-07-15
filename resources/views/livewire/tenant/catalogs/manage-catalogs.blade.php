<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6"
     x-data="{ 
        notifications: [], 
        addNotification(message, type = 'success') {
            const id = Date.now();
            this.notifications.push({ id, message, type });
            setTimeout(() => {
                this.removeNotification(id);
            }, 3000);
        },
        removeNotification(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        },
        copiarAlPortapapeles(url) {
            const temp = document.createElement('input');
            temp.value = url;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
            this.addNotification('Vínculo copiado al portapapeles: ' + url, 'success');
        }
     }"
     @show-toast.window="addNotification($event.detail.message, $event.detail.type)">

    <!-- Notification Toast Container -->
    <div class="fixed top-5 right-5 z-[100] flex flex-col gap-3">
        <template x-for="notification in notifications" :key="notification.id">
            <div 
                x-show="true"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-full opacity-0"
                class="flex items-center p-4 rounded-lg shadow-lg border-l-4 min-w-[300px]"
                :class="{
                    'bg-green-100 border-green-500 text-green-800 dark:bg-green-900 dark:text-green-200': notification.type === 'success',
                    'bg-red-100 border-red-500 text-red-800 dark:bg-red-900 dark:text-red-200': notification.type === 'error',
                    'bg-blue-100 border-blue-500 text-blue-800 dark:bg-blue-900 dark:text-blue-200': notification.type === 'info',
                    'bg-yellow-100 border-yellow-500 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': notification.type === 'warning'
                }"
            >
                <div class="flex-shrink-0 mr-3">
                    <x-heroicon-o-information-circle class="w-6 h-6" />
                </div>
                <div class="flex-1 font-medium text-sm" x-text="notification.message"></div>
                <button @click="removeNotification(notification.id)" class="ml-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>
        </template>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Catálogos</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Administra los catálogos PDF vinculados a tus familias de productos.</p>
        </div>
        <button wire:click="openCreateModal"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <x-heroicon-o-plus class="w-4 h-4 mr-2" />
            Agregar Catálogo
        </button>
    </div>

    <!-- Table Toolbar -->
    <div class="bg-white dark:bg-gray-800 rounded-t-lg border-t border-x border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Search bar -->
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por familia, título o archivo..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <!-- Page Size selector -->
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-700 dark:text-gray-300">Mostrar:</label>
                <select wire:model.live="perPage"
                    class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 px-3 py-1">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>

    <!-- DataTable Container -->
    <div class="bg-white dark:bg-gray-800 rounded-b-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th wire:click="sortBy('family')" class="cursor-pointer px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                        <div class="flex items-center gap-1">
                            Familia
                            @if ($sortField === 'family')
                                @if ($sortDirection === 'asc') <x-heroicon-o-chevron-up class="w-3.5 h-3.5" /> @else <x-heroicon-o-chevron-down class="w-3.5 h-3.5" /> @endif
                            @endif
                        </div>
                    </th>
                    <th wire:click="sortBy('title')" class="cursor-pointer px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                        <div class="flex items-center gap-1">
                            Título
                            @if ($sortField === 'title')
                                @if ($sortDirection === 'asc') <x-heroicon-o-chevron-up class="w-3.5 h-3.5" /> @else <x-heroicon-o-chevron-down class="w-3.5 h-3.5" /> @endif
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                        Nombre Archivo
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                        Vínculo
                    </th>
                    <th wire:click="sortBy('created_at')" class="cursor-pointer px-6 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1">
                            Fecha Reg
                            @if ($sortField === 'created_at')
                                @if ($sortDirection === 'asc') <x-heroicon-o-chevron-up class="w-3.5 h-3.5" /> @else <x-heroicon-o-chevron-down class="w-3.5 h-3.5" /> @endif
                            @endif
                        </div>
                    </th>
                    <th wire:click="sortBy('updated_at')" class="cursor-pointer px-6 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1">
                            Fecha Act
                            @if ($sortField === 'updated_at')
                                @if ($sortDirection === 'asc') <x-heroicon-o-chevron-up class="w-3.5 h-3.5" /> @else <x-heroicon-o-chevron-down class="w-3.5 h-3.5" /> @endif
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap w-24">
                        Opciones
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                @forelse($catalogs as $catalog)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-white font-medium">
                            {{ $catalog->family }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                            {{ $catalog->title }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                            {{ $catalog->file_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ asset($catalog->link) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 underline font-mono text-xs">
                                {{ $catalog->link }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                            {{ $catalog->created_at ? $catalog->created_at->format('d/m/Y H:i') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap text-gray-500 dark:text-gray-400">
                            {{ $catalog->updated_at ? $catalog->updated_at->format('d/m/Y H:i') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap flex items-center justify-center gap-2">
                            <button wire:click="edit({{ $catalog->id }})" class="p-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition-colors" title="Editar catálogo">
                                <x-heroicon-o-pencil class="w-4 h-4" />
                            </button>
                            <button @click="copiarAlPortapapeles('{{ url($catalog->link) }}')" class="p-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors" title="Copiar vínculo">
                                <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
                            </button>
                            <button @click="
                                Swal.fire({
                                    title: '¿Eliminar catálogo?',
                                    text: 'Esta acción no se puede deshacer.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#ef4444',
                                    cancelButtonColor: '#4f46e5',
                                    confirmButtonText: 'Sí, eliminar',
                                    cancelButtonText: 'Cancelar',
                                    background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                                    color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        $wire.delete({{ $catalog->id }})
                                    }
                                })
                            " class="p-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors" title="Eliminar catálogo">
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/10">
                            <div class="flex flex-col items-center">
                                <x-heroicon-o-folder-open class="w-12 h-12 mb-3 text-gray-400 dark:text-gray-600" />
                                <p class="text-base font-semibold">No se encontraron catálogos</p>
                                <p class="text-sm text-gray-400 dark:text-gray-500">Prueba ajustando el término de búsqueda o agrega uno nuevo.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <div class="mt-4">
        {{ $catalogs->links() }}
    </div>

    <!-- Modal Form (Create / Edit) -->
    <template x-teleport="body">
        <div x-data="{ show: @entangle('isOpen').live }"
             x-show="show"
             x-cloak
             style="display:none;"
             class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
            
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="$wire.cancel()"></div>

            <!-- Modal Content -->
            <div class="relative z-10 bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col max-h-[90vh]">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $selectedCatalogId ? 'Editar Catálogo' : 'Agregar Catálogo' }}
                    </h3>
                    <button @click="$wire.cancel()" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <!-- Modal Body -->
                    <div class="p-6 space-y-4 overflow-y-auto flex-1">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Familia / Categoría <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="family" {{ $selectedCatalogId ? 'readonly' : '' }}
                                   class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all dark:text-white {{ $selectedCatalogId ? 'cursor-not-allowed opacity-70 bg-gray-100 dark:bg-gray-700' : '' }}" 
                                   placeholder="Ej: Fuentes metálicas">
                            @error('family') <span class="text-red-500 text-[10px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Título del Catálogo <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="title" {{ $selectedCatalogId ? 'readonly' : '' }}
                                   class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all dark:text-white {{ $selectedCatalogId ? 'cursor-not-allowed opacity-70 bg-gray-100 dark:bg-gray-700' : '' }}" 
                                   placeholder="Ej: Catálogo Fuentes 2026">
                            @error('title') <span class="text-red-500 text-[10px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Archivo PDF <span class="text-red-500">{{ $selectedCatalogId ? '' : '*' }}</span></label>
                            @if($archivoActual)
                                <div class="mb-2 p-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-xs flex items-center justify-between text-gray-600 dark:text-gray-300">
                                    <span class="truncate">Archivo actual: {{ basename($archivoActual) }}</span>
                                    <a href="{{ asset($archivoActual) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Ver</a>
                                </div>
                            @endif
                            <input type="file" wire:model="archivo" accept=".pdf"
                                   class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all dark:text-white">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">Solo se permiten archivos PDF de hasta 20 MB.</p>
                            @error('archivo') <span class="text-red-500 text-[10px] mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 flex justify-end gap-2 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="$wire.cancel()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-all focus:outline-none disabled:opacity-50">
                            <span wire:loading class="mr-2">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>
