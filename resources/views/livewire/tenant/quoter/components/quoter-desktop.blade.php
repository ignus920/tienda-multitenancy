<!-- Wrapper con padding y background -->
<div class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Cotizaciones</h1>
                <p class="text-gray-600 dark:text-slate-400 text-sm mt-1">Gestión de registros</p>
            </div>
            <div class="flex items-center space-x-3">
            

                <button
                    wire:click="nuevaCotizacion"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded text-sm font-medium flex items-center transition-colors"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nueva Cotización
                </button>
            </div>
        </div>
    </div>

    <!-- Toolbar Card -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-4 mb-6 border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <!-- Search Section -->
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live="search"
                        placeholder="Buscar registros..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-gray-900 dark:text-slate-200 placeholder-gray-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-slate-600 text-sm transition-colors"
                    >
                </div>
            </div>

            <!-- Actions Section -->
            <div class="flex items-center space-x-3">
                <!-- Registros por página -->
                <div class="flex items-center gap-2">
                            <label class="text-sm text-gray-700 dark:text-gray-300">Mostrar:</label>
                            <select wire:model.live="perPage"
                                    class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>

                <!-- Botones de exportar -->
                        <div class="flex items-center gap-2">
                            <!-- Botón Excel -->
                            <button wire:click="exportExcel"
                                    title="Exportar a Excel"
                                    class="inline-flex items-center justify-center p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M21,5V19A2,2 0 0,1 19,21H5A2,2 0 0,1 3,19V5A2,2 0 0,1 5,3H19A2,2 0 0,1 21,5M19,5H12V7H19V5M19,9H12V11H19V9M19,13H12V15H19V13M19,17H12V19H19V17M5,5V7H10V5H5M5,9V11H10V9H5M5,13V15H10V13H5M5,17V19H10V17H5Z"/>
                                </svg>
                            </button>
                            <!-- Botón PDF -->
                            <button wire:click="exportPdf"
                                    title="Exportar a PDF"
                                    class="inline-flex items-center justify-center p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                                </svg>
                            </button>
                            <!-- Botón CSV -->
                            <button wire:click="exportCsv"
                                    title="Exportar a CSV"
                                    class="inline-flex items-center justify-center p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M8,12V14H16V12H8M8,16V18H13V16H8Z"/>
                                </svg>
                            </button>

                            @if($this->canPrint())
                                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition-colors"
                                                wire:click=""
                                                title="Imprimir cotización">
                                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                                            </svg>
                                            Imprimir
                                        </button>
                                    @endif
                        </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 px-4 py-3 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-slate-800 rounded-lg overflow-hidden border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="overflow-x-auto">

            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-slate-700">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>ID</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase tracking-wider cursor-pointer hover:text-gray-700 dark:hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>RAZÓN SOCIAL</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider cursor-pointer hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>IDENTIFICACIÓN</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider cursor-pointer hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>SUCURSAL</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider cursor-pointer hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>DIRECCIÓN</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider cursor-pointer hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>TELÉFONO</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider cursor-pointer hover:text-slate-300 transition-colors">
                            <div class="flex items-center space-x-1">
                                <span>FECHA</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-slate-400 uppercase tracking-wider">
                            ACCIONES
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotes as $quote)
                        <tr class="border-b border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                #{{ $quote->consecutive }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300">
                                Usuario común
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300">
                                {{ $quote->customerId }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300">
                                Sucursal
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300">
                                Calle 123#1a-20
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300">
                                333333333<br>333333333
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-slate-300">
                                {{ $quote->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center items-center space-x-2">
                                    <button class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-xs transition-colors">
                                        Editar
                                    </button>

                                    {{-- DEBUG: Mostrar estado del permiso temporalmente --}}
                                    <span class="text-xs text-red-500 border border-red-300 px-1 rounded">
                                        Print: {{ $this->canPrint() ? 'SI' : 'NO' }} (Límite: {{ $this->getPrintCopiesLimit() }})
                                    </span>

                                    @if($this->canPrint())
                                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition-colors"
                                                wire:click="printQuote({{ $quote->id }})"
                                                title="Imprimir cotización">
                                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
                                            </svg>
                                            Imprimir
                                        </button>
                                    @endif

                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs transition-colors"
                                            wire:click="eliminar({{ $quote->id }})"
                                            onclick="return confirm('¿Está seguro de eliminar esta cotización?')">
                                        Eliminar
                                    </button>
                                    <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs transition-colors"
                                            wire:click="nuevaCotizacion">
                                        + Sucursal
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-gray-500 dark:text-slate-400">
                                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-700 dark:text-slate-300 mb-2">No hay registros</h3>
                                    <p class="mb-6">
                                        @if($search)
                                            No se encontraron registros que coincidan con "{{ $search }}".
                                        @else
                                            Comienza creando tu primer registro.
                                        @endif
                                    </p>
                                    @if(!$search)
                                        <button
                                            wire:click="nuevaCotizacion"
                                            class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded font-medium transition-colors"
                                        >
                                            Crear Primer Registro
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        @if($quotes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600 dark:text-slate-300">
                        Mostrando <span class="font-medium">{{ $quotes->firstItem() ?? 0 }}</span> a
                        <span class="font-medium">{{ $quotes->lastItem() ?? 0 }}</span> de
                        <span class="font-medium">{{ $quotes->total() }}</span> resultados
                    </div>
                    <div>
                        {{ $quotes->links() }}
                    </div>
                </div>
            </div>
        @endif
        </div>
    </div>
</div>