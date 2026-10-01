<div>
    <div class="px-4 py-6 sm:px-6 lg:px-8 max-w-7xl mx-auto">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Sobrantes de Laboratorio</h1>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                    Control de inventario en centímetros de los recortes de materiales que quedan en laboratorio tras realizar ensambles.
                </p>
            </div>
        </div>

        <div class="mt-8 flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">Código / SKU</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Nombre del Insumo</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Sobrante Disponible</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Largo Original</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                @forelse($leftovers as $left)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                            {{ $left->item->internal_code ?? $left->item->sku ?? 'N/A' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $left->item->name ?? $left->item->display_name ?? 'Insumo eliminado' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 font-bold">
                                            <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-900/30 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-400 ring-1 ring-inset ring-blue-700/10">
                                                {{ number_format($left->available_cm, 0) }} cm
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            @if($left->item && $left->item->dimensions && $left->item->dimensions->long > 0)
                                                {{ number_format($left->item->dimensions->long, 0) }} cm
                                            @else
                                                <span class="text-xs italic text-gray-400">No definido</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No hay sobrantes registrados en el laboratorio todavía. Los sobrantes aparecerán cuando se creen remisiones de productos ensamblados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
