<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-12xl mx-auto">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Gestión de Importaciones
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Administración de items del sistema</p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-start justify-start sm:justify-between gap-4">
                    <!-- Botón Principal -->
                    <button wire:click="showModalRegis"
                        class="inline-flex items-center px-4 py-2 
                        bg-indigo-600 hover:bg-indigo-700 
                        dark:bg-indigo-500 dark:hover:bg-indigo-600
                        border border-transparent rounded-lg 
                        font-semibold text-xs text-white uppercase tracking-widest 
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 
                        focus:ring-offset-2 dark:focus:ring-offset-gray-800 
                        transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4"></path>
                        </svg>
                        Agregar Nuevo Item
                    </button>
                {{-- </div> --}}

                <div class="flex flex-col sm:flex-row items-start sm:items-start justify-start sm:justify-between gap-4">
                    <!-- Administrar Etiquetas -->
                    <a href="{{ route('imports.imports-labels' )}}"
                        class="inline-flex items-center px-4 py-2 
                        bg-emerald-500 hover:bg-emerald-600 
                        dark:bg-emerald-600 dark:hover:bg-emerald-500
                        text-white rounded-lg font-semibold text-xs uppercase
                        border border-transparent
                        focus:outline-none focus:ring-2 focus:ring-emerald-400 
                        focus:ring-offset-2 dark:focus:ring-offset-gray-800
                        transition-all duration-200">
                        <x-heroicon-o-tag class="w-5 h-5 mr-2" />
                        Administrar Etiquetas
                    </a>
                    <!-- Instrucciones -->
                    <button wire:click=""
                        class="inline-flex items-center px-4 py-2 
                        bg-gray-200 hover:bg-gray-300 
                        dark:bg-gray-700 dark:hover:bg-gray-600
                        text-gray-800 dark:text-gray-200
                        rounded-lg font-semibold text-xs uppercase
                        border border-gray-300 dark:border-gray-600
                        focus:outline-none focus:ring-2 focus:ring-gray-400
                        focus:ring-offset-2 dark:focus:ring-offset-gray-800
                        transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 20V4m0 0l4 4m-4-4l-4 4"></path>
                        </svg>
                        Instrucciones
                    </button>
                </div>
            </div>
        </div>

        <!-- Import List Component -->
        @livewire('tenant.imports.import-list')
    </div>
    
    @if ($showModalRegisItem)
        <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-75 overflow-y-auto h-full w-full z-50"
            x-data="{ show: true }" x-show="show" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <!-- Header -->
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Crear Item
                            </h3>
                        </div>
                        <button wire:click="cancel"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    @livewire('tenant.imports.import-reg-item')
                </div>
            </div>
        </div>
    @endif
</div>