<div class="min-h-screen bg-gray-50 dark:bg-gray-900 ">
    <div class="max-w-md mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            
        </div>

        <!-- Search Input and Add Button - Sticky -->
        <div class="sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="px-4 py-4">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 p-2">Nueva venta</h1>
                <div class="flex gap-3 mb-4">
                    <input
                        type="text"
                        wire:model.live="search"
                        placeholder="Buscar cotización"
                        class="flex-1 p-3 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                    >
                    <button
                        wire:click="nuevaCotizacion"
                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg shadow-sm flex items-center justify-center min-w-[52px]"
                        title="Nueva Cotización"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                </div>

                <!-- Cotizaciones Title -->
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Cotizaciones</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Desliza para ver opciones</span>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="px-4 py-4">
            <!-- Success Message -->
            @if (session()->has('message'))
                <div class="bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4">
                    {{ session('message') }}
                </div>
            @endif

            <!-- Quotes List -->
            <div class="space-y-4">
            @forelse($quotes as $quote)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <!-- Quote Header -->
                    <div class="bg-gray-800 dark:bg-gray-700 text-white p-3 rounded-t-lg">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">Venta: #{{ $quote->consecutive }}</span>
                            <span class="text-sm">{{ $quote->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <!-- Quote Content -->
                    <div class="p-4">
                        <div class="mb-3">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Cliente #{{ $quote->customerId }}</p>
                            <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">Total: ${{ number_format($quote->total, 0, ',', '.') }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-between items-center">
                            <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                Opciones
                            </button>
                            <button
                                wire:click="eliminar({{ $quote->id }})"
                                onclick="return confirm('¿Está seguro de eliminar esta cotización?')"
                                class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">No se encontraron cotizaciones</p>
                </div>
            @endforelse
        </div>

            <!-- Pagination -->
            @if($quotes->hasPages())
                <div class="mt-6">
                    {{ $quotes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>