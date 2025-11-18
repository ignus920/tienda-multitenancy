<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header con información del tenant -->
        <div class="bg-white dark:bg-gray-900 dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg mb-6 ">
            <div class="p-6 bg-white dark:bg-gray-900 dark:bg-gray-900 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white dark:text-white">{{ $tenant->name }}</h2>
                        <p class="text-sm text-gray-900 dark:text-white mt-1">Bienvenido, {{ $user->name }}</p>
                    </div>
                    <div class="flex space-x-4">
                        <button
                            wire:click="switchTenant"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm font-medium dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            Cambiar Empresa
                        </button>
                     
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Ventas Hoy -->
            <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">Ventas Hoy</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($stats['total_ventas_hoy'], 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Clientes -->
            <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">Total Clientes</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_clientes'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Productos -->
            <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">Total Productos</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total_productos'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ventas del Mes -->
            <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">Ventas del Mes</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">${{ number_format($stats['ventas_mes'], 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accesos Rápidos -->
        <div class="bg-white dark:bg-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white dark:bg-gray-900 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Accesos Rápidos
                    <span class="text-sm font-normal text-gray-500">({{ count($enabledFeatures) }} funciones habilitadas)</span>
                </h3>

                @if(count($enabledFeatures) > 0)
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($enabledFeatures as $key => $feature)
                            @php
                                $colors = [
                                    'ventas' => ['bg' => 'indigo', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                                    'clientes' => ['bg' => 'green', 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                                    'productos' => ['bg' => 'yellow', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                                    'caja' => ['bg' => 'purple', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                                    'inventario' => ['bg' => 'blue', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h1.586a1 1 0 01.707.293l1.414 1.414a1 1 0 00.707.293H15a2 2 0 110 4M5 8v10a2 2 0 002 2h8a2 2 0 002-2V8m-9 4h4'],
                                    'reportes' => ['bg' => 'red', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z']
                                ];
                                $color = $colors[$key] ?? ['bg' => 'gray', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'];
                                $bgColor = $color['bg'];
                                $iconPath = $color['icon'];

                                $href = str_starts_with($feature['route'], 'http') || str_starts_with($feature['route'], '/') ? $feature['route'] : route($feature['route']);
                            @endphp

                            <a href="{{ $href }}" class="group flex flex-col items-center p-6 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 ease-in-out">
                                @if($bgColor === 'indigo')
                                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-full group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800/50 group-hover:scale-110 transition-all duration-300">
                                        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 group-hover:text-indigo-700 dark:group-hover:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
                                        </svg>
                                    </div>
                                @elseif($bgColor === 'green')
                                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full group-hover:bg-green-200 dark:group-hover:bg-green-800/50 group-hover:scale-110 transition-all duration-300">
                                        <svg class="w-8 h-8 text-green-600 dark:text-green-400 group-hover:text-green-700 dark:group-hover:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
                                        </svg>
                                    </div>
                                @elseif($bgColor === 'yellow')
                                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-full group-hover:bg-yellow-200 dark:group-hover:bg-yellow-800/50 group-hover:scale-110 transition-all duration-300">
                                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 group-hover:text-yellow-700 dark:group-hover:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
                                        </svg>
                                    </div>
                                @elseif($bgColor === 'purple')
                                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-full group-hover:bg-purple-200 dark:group-hover:bg-purple-800/50 group-hover:scale-110 transition-all duration-300">
                                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400 group-hover:text-purple-700 dark:group-hover:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
                                        </svg>
                                    </div>
                                @elseif($bgColor === 'blue')
                                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full group-hover:bg-blue-200 dark:group-hover:bg-blue-800/50 group-hover:scale-110 transition-all duration-300">
                                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
                                        </svg>
                                    </div>
                                @elseif($bgColor === 'red')
                                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-full group-hover:bg-red-200 dark:group-hover:bg-red-800/50 group-hover:scale-110 transition-all duration-300">
                                        <svg class="w-8 h-8 text-red-600 dark:text-red-400 group-hover:text-red-700 dark:group-hover:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="p-3 bg-gray-100 dark:bg-gray-900/30 rounded-full group-hover:bg-gray-200 dark:group-hover:bg-gray-800/50 group-hover:scale-110 transition-all duration-300">
                                        <svg class="w-8 h-8 text-gray-600 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"></path>
                                        </svg>
                                    </div>
                                @endif
                                <span class="mt-3 text-sm font-medium text-gray-900 dark:text-white transition-colors duration-300">{{ $feature['name'] }}</span>
                                <span class="text-xs text-gray-500 mt-1">Opción {{ $feature['option_id'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">No hay funcionalidades habilitadas</h3>
                                <p class="text-sm text-yellow-700 mt-1">Contacta al administrador para habilitar funcionalidades en tu plan.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Información adicional -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Este es el panel de control de <strong>{{ $tenant->name }}</strong>.
                        Aquí podrá gestionar todas las operaciones de su empresa.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
