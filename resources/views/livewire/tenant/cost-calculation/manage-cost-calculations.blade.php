<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="w-full">

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 mb-4">
            <div class="flex justify-between items-center flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg font-bold text-gray-900 dark:text-white">Cálculo de Costos</h1>
                            <button wire:click="openInstructivo" class="text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50 px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-800 transition-colors">
                                Instructivo
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Cotizaciones armadas con inventario del ERP (por unidad o por centímetro) y productos externos.</p>
                    </div>
                </div>
                <a href="{{ route('tenant.cost-calculations.create') }}" wire:navigate
                    class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition-colors">
                    + Crear Nuevo
                </a>
            </div>

            <div class="mt-4">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre..."
                    class="w-full max-w-sm border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-2xs text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-850">
                            <th class="text-left py-3 px-4">Nombre</th>
                            <th class="text-left py-3 px-4">Creado por</th>
                            <th class="text-left py-3 px-4">Última edición</th>
                            <th class="text-right py-3 px-4">Total (al guardar)</th>
                            <th class="text-right py-3 px-4 w-28">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($calculations as $calc)
                        <tr class="border-b border-gray-50 dark:border-gray-750 hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="py-3 px-4 font-semibold text-gray-800 dark:text-gray-200">{{ $calc->name }}</td>
                            <td class="py-3 px-4 text-gray-500 dark:text-gray-400">{{ $calc->creator->name ?? 'Usuario' }}</td>
                            <td class="py-3 px-4 text-gray-500 dark:text-gray-400">{{ ($calc->updated_at ?? $calc->created_at)?->format('d/m/Y h:i A') }}</td>
                            <td class="py-3 px-4 text-right font-semibold text-gray-800 dark:text-gray-200">${{ number_format($calc->items->sum('line_cost'), 2) }}</td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('tenant.cost-calculations.edit', ['calculationId' => $calc->id]) }}" wire:navigate
                                    class="text-indigo-600 hover:text-indigo-700 font-semibold text-2xs">
                                    {{ $this->canEditCalc($calc) ? 'Editar' : 'Ver' }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-gray-400 text-xs">Aún no has creado ningún cálculo de costos.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($calculations->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $calculations->links() }}
            </div>
            @endif
        </div>

    </div>
</div>
