<div class="min-h-screen bg-gray-50 p-4">
    <div class="max-w-md mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Nueva venta</h1>
        </div>

        <!-- Search Input -->
        <div class="mb-4">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar cotización"
                class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
        </div>

        <!-- Cotizaciones Title and Add Button -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Cotizaciones</h2>
            <button
                wire:click="nuevaCotizacion"
                class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg shadow-sm"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
        </div>

        <!-- Success Message -->
        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        <!-- Quotes List -->
        <div class="space-y-4">
            @forelse($quotes as $quote)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <!-- Quote Header -->
                    <div class="bg-gray-800 text-white p-3 rounded-t-lg">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">Venta: #{{ $quote->consecutive }}</span>
                            <span class="text-sm">{{ $quote->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <!-- Quote Content -->
                    <div class="p-4">
                        <div class="mb-3">
                            <p class="text-sm text-gray-600">Cliente #{{ $quote->customerId }}</p>
                            <p class="text-lg font-semibold text-gray-800">Total: ${{ number_format($quote->total, 0, ',', '.') }}</p>
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
                    <p class="text-gray-500">No se encontraron cotizaciones</p>
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