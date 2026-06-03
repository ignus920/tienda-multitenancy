<div class="w-full px-4 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fab fa-wordpress text-indigo-500"></i>
                Sincronización de Stock con WordPress
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Bodega: PRINCIPAL · Precio: Precio Regular + IVA · Descuenta reservas REGISTRADO
            </p>
        </div>
    </div>

    <!-- Buscador -->
    <div class="mb-4 flex gap-3">
        <div class="relative flex-1">
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="Buscar por nombre, SKU o código interno..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <button wire:click="selectAll"
            class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            Seleccionar todos
        </button>
        <button wire:click="clearSelection"
            class="px-4 py-2 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
            Limpiar
        </button>
    </div>

    <!-- Tabla de items -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mb-6">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-10"></th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">SKU</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Stock Bruto</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Reservas</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Stock Neto</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Stock Mín.</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">%</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-indigo-600 uppercase">→ WP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($items as $item)
                    @php $isSelected = in_array($item['id'], $selected); @endphp
                    <tr wire:click="toggleSelect({{ $item['id'] }})"
                        class="cursor-pointer transition-colors {{ $isSelected ? 'bg-indigo-50 dark:bg-indigo-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" {{ $isSelected ? 'checked' : '' }} readonly
                                class="w-4 h-4 text-indigo-600 rounded border-gray-300 pointer-events-none">
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-xs">
                                {{ $item['name'] }}
                            </div>
                            @if($item['internal_code'])
                                <div class="text-xs text-gray-400">{{ $item['internal_code'] }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-mono text-indigo-600 dark:text-indigo-400">
                            {{ $item['sku'] }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-700 dark:text-gray-300">
                            {{ number_format($item['stock'], 0) }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm {{ $item['reservas'] > 0 ? 'text-orange-500 font-semibold' : 'text-gray-400' }}">
                            {{ number_format($item['reservas'], 0) }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold {{ $item['stock_neto'] > $item['stock_min'] ? 'text-green-600 dark:text-green-400' : 'text-red-500' }}">
                            {{ number_format($item['stock_neto'], 0) }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ number_format($item['stock_min'], 0) }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ $item['porcentaje'] }}%
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-sm font-black
                                {{ $item['stock_wp'] > 0 ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400' }}">
                                {{ number_format($item['stock_wp'], 0) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-gray-400 text-sm italic">
                            No se encontraron items con SKU asignado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Botón de sync -->
    <div class="flex items-center justify-between">
        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ count($selected) }} item(s) seleccionado(s)
        </span>
        <button wire:click="syncSelected"
                wire:loading.attr="disabled"
                @class([
                    'inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-white transition-all',
                    'bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 dark:shadow-none' => count($selected) > 0,
                    'bg-gray-300 dark:bg-gray-600 cursor-not-allowed' => count($selected) === 0,
                ])>
            <span wire:loading.remove wire:target="syncSelected">
                <i class="fab fa-wordpress mr-1"></i>
                Sincronizar Stock Seleccionado
            </span>
            <span wire:loading wire:target="syncSelected" class="flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Sincronizando...
            </span>
        </button>
    </div>

    <!-- Resultados -->
    @if(!empty($results))
        <div class="mt-8">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Resultados de la sincronización</h2>
            <div class="space-y-2">
                @foreach($results as $r)
                    <div class="flex items-center gap-4 p-3 rounded-lg border {{ $r['success'] ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' }}">
                        <span class="{{ $r['success'] ? 'text-green-500' : 'text-red-500' }} text-lg">
                            {{ $r['success'] ? '✅' : '❌' }}
                        </span>
                        <div class="flex-1">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">SKU: {{ $r['sku'] }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">{{ $r['message'] }}</span>
                        </div>
                        @if($r['success'])
                            <div class="flex gap-4 text-xs text-gray-500 dark:text-gray-400">
                                <span>Stock bruto: <strong>{{ $r['stock_bruto'] }}</strong></span>
                                <span>Reservas: <strong class="text-orange-500">{{ $r['reservas'] }}</strong></span>
                                <span>→ WP: <strong class="text-indigo-600 dark:text-indigo-400">{{ $r['stock_wp'] }}</strong></span>
                                @if($r['precio_wp'] > 0)
                                    <span>Precio: <strong class="text-green-600">${{ number_format($r['precio_wp'], 0, ',', '.') }}</strong></span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
