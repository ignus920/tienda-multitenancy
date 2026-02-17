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
    <button wire:click=""
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


    <div class="flex flex-col sm:flex-row items-start sm:items-start justify-start sm:justify-between gap-4">

        <!-- Administrar Etiquetas -->
        <button wire:click=""
            class="inline-flex items-center px-4 py-2 
            bg-emerald-500 hover:bg-emerald-600 
            dark:bg-emerald-600 dark:hover:bg-emerald-500
            text-white rounded-lg font-semibold text-xs uppercase
            border border-transparent
            focus:outline-none focus:ring-2 focus:ring-emerald-400 
            focus:ring-offset-2 dark:focus:ring-offset-gray-800
            transition-all duration-200">

            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v16m0 0l-4-4m4 4l4-4"></path>
            </svg>
            Administrar Etiquetas
        </button>


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
</div>