<div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Icono con Gradiente -->
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 shadow-lg shadow-indigo-500/30 ring-4 ring-indigo-50 dark:ring-indigo-900/20">
            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>
        
        <h2 class="mt-6 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">
            Selecciona tu empresa
        </h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Bienvenido, selecciona la sucursal con la que deseas trabajar hoy.
        </p>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-xl">
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow-xl shadow-gray-200/50 dark:shadow-none sm:rounded-2xl sm:px-10 border border-gray-100 dark:border-gray-700">
            
            @if (session()->has('error'))
                <div class="mb-4 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @forelse($tenants as $tenant)
                    <button 
                        wire:click="selectTenant('{{ $tenant->id }}')"
                        wire:loading.attr="disabled"
                        class="group relative flex items-center p-4 rounded-xl border-2 border-gray-50 dark:border-gray-700 bg-gray-50 dark:bg-gray-750 hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-all duration-300 text-left focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                    >
                        <div class="flex-shrink-0 flex h-12 w-12 items-center justify-center rounded-xl bg-white dark:bg-gray-700 shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                            @if($tenant->logo_url)
                                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-8 w-8 object-contain">
                            @else
                                <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400 group-hover:text-white">
                                    {{ substr($tenant->name, 0, 1) }}
                                </span>
                            @endif
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                {{ $tenant->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ $tenant->id }}
                            </p>
                        </div>
                        
                        <!-- Flecha de acción -->
                        <div class="absolute right-4 opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </div>

                        <!-- Indicador de Carga -->
                        <div wire:loading wire:target="selectTenant('{{ $tenant->id }}')" class="absolute inset-0 bg-white/60 dark:bg-gray-800/60 rounded-xl flex items-center justify-center">
                            <svg class="animate-spin h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full py-8 text-center bg-gray-50 dark:bg-gray-900 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">No se encontraron empresas activas.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8 border-t border-gray-100 dark:border-gray-700 pt-6 flex justify-between items-center">
                <button 
                    wire:click="logout"
                    class="text-sm font-medium text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors flex items-center"
                >
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Cerrar sesión
                </button>
                
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    &copy; {{ date('Y') }} {{ config('app.name') }}
                </span>
            </div>
        </div>
    </div>
</div>
