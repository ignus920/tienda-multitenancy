@php
    $statusMeta = [
        'pendiente'  => ['label' => 'Pendiente',  'cell' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',       'dot' => 'bg-red-500'],
        'en_proceso' => ['label' => 'En proceso', 'cell' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300', 'dot' => 'bg-amber-500'],
        'listo'      => ['label' => 'Listo',      'cell' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300', 'dot' => 'bg-green-500'],
        'terminado'  => ['label' => 'Terminado',  'cell' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300', 'dot' => 'bg-green-500'],
    ];
@endphp

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-4 sm:p-6">
    <div class="max-w-[1600px] mx-auto">

        @if (session()->has('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="mb-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-lg shadow">
                <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p>
            </div>
        @endif

        @unless ($showDetail)
        <!-- Encabezado -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestión de Videos</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">
                        Solicitudes de video de productos y seguimiento de su publicación en cada canal.
                    </p>
                </div>
                @if ($this->canCreate)
                    <button wire:click="openCreateModal"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-xs uppercase tracking-widest transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Solicitar video
                    </button>
                @endif
            </div>
        </div>

        <!-- Accesos rápidos por estado (vista del Gestor) -->
        @php
            $quickTabs = [
                ''           => ['label' => 'Todos',      'count' => $statusCounts['todos'],      'ring' => 'ring-indigo-500', 'active' => 'bg-indigo-600 text-white', 'badge' => 'bg-white/20'],
                'pendiente'  => ['label' => 'Pendientes', 'count' => $statusCounts['pendiente'],  'ring' => 'ring-red-500',    'active' => 'bg-red-600 text-white',    'badge' => 'bg-white/20'],
                'en_proceso' => ['label' => 'En proceso', 'count' => $statusCounts['en_proceso'], 'ring' => 'ring-amber-500',  'active' => 'bg-amber-500 text-white',  'badge' => 'bg-white/20'],
                'terminado'  => ['label' => 'Terminados', 'count' => $statusCounts['terminado'],  'ring' => 'ring-green-500',  'active' => 'bg-green-600 text-white',  'badge' => 'bg-white/20'],
            ];
        @endphp
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach ($quickTabs as $value => $tab)
                @php $isActive = $statusFilter === $value; @endphp
                <button
                    wire:click="filterByStatus('{{ $value }}')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-bold border transition
                        {{ $isActive
                            ? $tab['active'] . ' border-transparent shadow-sm'
                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:ring-2 ' . $tab['ring'] }}">
                    {{ $tab['label'] }}
                    <span class="inline-flex items-center justify-center min-w-[1.5rem] px-1.5 py-0.5 rounded-full text-[11px] tabular-nums
                        {{ $isActive ? $tab['badge'] : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300' }}">
                        {{ $tab['count'] }}
                    </span>
                </button>
            @endforeach
        </div>

        <!-- Barra de filtros -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-5">
            <div class="flex flex-col lg:flex-row lg:items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Buscar</label>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="N° solicitud, código o descripción del producto…"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Estado</label>
                    <select wire:model.live="statusFilter"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white py-2 pl-3 pr-8 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En proceso</option>
                        <option value="terminado">Terminado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Actividad pendiente</label>
                    <select wire:model.live="channelFilter"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white py-2 pl-3 pr-8 focus:ring-2 focus:ring-indigo-500">
                        <option value="">Cualquiera</option>
                        <option value="sin_celular">Sin video en celular</option>
                        <option value="sin_youtube">Sin video en YouTube</option>
                        <option value="sin_web">Sin video en página web</option>
                        <option value="sin_tiktok">Sin video en TikTok</option>
                        <option value="sin_instagram">Sin video en Instagram</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Desde</label>
                    <input wire:model.live="dateFrom" type="date"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white py-2 px-3 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Hasta</label>
                    <input wire:model.live="dateTo" type="date"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white py-2 px-3 focus:ring-2 focus:ring-indigo-500">
                </div>

                @if ($search || $statusFilter || $channelFilter || $dateFrom || $dateTo)
                    <button wire:click="clearFilters"
                        class="px-3 py-2 text-xs font-bold text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Limpiar
                    </button>
                @endif

                <div class="flex items-center gap-1 rounded-lg border border-gray-300 dark:border-gray-600 p-1 lg:ml-auto">
                    <button wire:click="setView('matriz')"
                        class="px-3 py-1.5 text-xs font-bold rounded-md transition {{ $viewMode === 'matriz' ? 'bg-indigo-600 text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        Matriz
                    </button>
                    <button wire:click="setView('lista')"
                        class="px-3 py-1.5 text-xs font-bold rounded-md transition {{ $viewMode === 'lista' ? 'bg-indigo-600 text-white' : 'text-gray-500 dark:text-gray-400' }}">
                        Lista
                    </button>
                </div>
            </div>

            <!-- Leyenda de colores -->
            <div class="flex flex-wrap items-center gap-4 mt-3 text-xs text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Pendiente</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> En proceso</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span> Listo</span>
            </div>
        </div>

        <!-- ══════════ VISTA MATRIZ ══════════ -->
        @if ($viewMode === 'matriz')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="sticky left-0 z-20 bg-gray-50 dark:bg-gray-900 px-4 py-3 text-left min-w-[260px] border-r border-gray-200 dark:border-gray-700">
                                    <button wire:click="sortBy('product_code')" class="flex items-center gap-1 hover:text-gray-800 dark:hover:text-gray-200">
                                        Producto / Solicitud
                                    </button>
                                </th>
                                <th class="px-3 py-3 text-left whitespace-nowrap">
                                    <button wire:click="sortBy('created_at')" class="flex items-center gap-1 hover:text-gray-800 dark:hover:text-gray-200">Fecha</button>
                                </th>
                                <th class="px-3 py-3 text-left whitespace-nowrap">Solicitante</th>
                                <th class="px-3 py-3 text-left whitespace-nowrap">Gestor</th>
                                @foreach ($channels as $key => $cfg)
                                    <th class="px-3 py-3 text-center whitespace-nowrap min-w-[130px]">{{ $cfg['label'] }}</th>
                                @endforeach
                                <th class="px-3 py-3 text-center whitespace-nowrap">
                                    <button wire:click="sortBy('status')" class="hover:text-gray-800 dark:hover:text-gray-200">Estado</button>
                                </th>
                                <th class="px-3 py-3 text-center whitespace-nowrap">
                                    <button wire:click="sortBy('progress')" class="hover:text-gray-800 dark:hover:text-gray-200">Avance</button>
                                </th>
                                <th class="px-3 py-3 text-center whitespace-nowrap">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($requests as $req)
                                @php
                                    $tasksByChannel = $req->tasks->keyBy('channel');
                                    $sm = $statusMeta[$req->status] ?? $statusMeta['pendiente'];
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-4 py-3 min-w-[260px] border-r border-gray-200 dark:border-gray-700">
                                        <a href="{{ route('items', ['search' => $req->product_code_actual]) }}" target="_blank"
                                            class="font-bold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline">
                                            {{ $req->product_code_actual ?? '—' }} ↗
                                        </a>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ $req->product_name_actual }}</div>
                                        <div class="text-[11px] font-mono text-indigo-600 dark:text-indigo-400 mt-1">{{ $req->request_number }}</div>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                        {{ $req->created_at?->format('d/m/Y') }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300">
                                        {{ $userNames[$req->requested_by] ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-600 dark:text-gray-300">
                                        {{ $req->gestor_id ? ($userNames[$req->gestor_id] ?? 'Asignado') : 'Sin asignar' }}
                                    </td>

                                    @foreach ($channels as $key => $cfg)
                                        @php
                                            $task = $tasksByChannel->get($key);
                                            $ts = $statusMeta[$task->status ?? 'pendiente'] ?? $statusMeta['pendiente'];
                                        @endphp
                                        <td class="px-3 py-3 text-center">
                                            <div class="inline-flex flex-col items-center gap-1">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $ts['cell'] }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $ts['dot'] }}"></span>
                                                    {{ $ts['label'] }}
                                                </span>
                                                @if ($cfg['requires_link'] && !empty($task?->link))
                                                    <a href="{{ $task->link }}" target="_blank" rel="noopener"
                                                        class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline">Abrir enlace</a>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach

                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $sm['cell'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $sm['dot'] }}"></span>
                                            {{ $sm['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200 tabular-nums">
                                            {{ $req->progress_done }} de {{ $req->progress_total }} — {{ $req->progress_percent }}%
                                        </div>
                                        <div class="w-24 h-1.5 mx-auto mt-1 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-full bg-indigo-500" style="width: {{ $req->progress_percent }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                        <button wire:click="openDetail({{ $req->id }})"
                                            class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-lg text-xs font-bold hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition">
                                            Ver
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 7 + count($channels) }}" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">
                                        No hay solicitudes de video que coincidan con los filtros.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $requests->links() }}
                </div>
            </div>
        @else
            <!-- ══════════ VISTA LISTA (tabla tradicional) ══════════ -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <button wire:click="sortBy('request_number')" class="hover:text-gray-800 dark:hover:text-gray-200"># Solicitud</button>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <button wire:click="sortBy('created_at')" class="hover:text-gray-800 dark:hover:text-gray-200">Fecha solicitud</button>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <button wire:click="sortBy('product_code')" class="hover:text-gray-800 dark:hover:text-gray-200">Código</button>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <button wire:click="sortBy('product_name')" class="hover:text-gray-800 dark:hover:text-gray-200">Producto</button>
                                </th>
                                <th class="px-4 py-3 text-left">Solicitado por</th>
                                <th class="px-4 py-3 text-left">Gestor</th>
                                <th class="px-4 py-3 text-center">
                                    <button wire:click="sortBy('status')" class="hover:text-gray-800 dark:hover:text-gray-200">Estado</button>
                                </th>
                                <th class="px-4 py-3 text-center">
                                    <button wire:click="sortBy('progress')" class="hover:text-gray-800 dark:hover:text-gray-200">Avance</button>
                                </th>
                                <th class="px-4 py-3 text-left">
                                    <button wire:click="sortBy('updated_at')" class="hover:text-gray-800 dark:hover:text-gray-200">Últ. modif.</button>
                                </th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($requests as $req)
                                @php $sm = $statusMeta[$req->status] ?? $statusMeta['pendiente']; @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="px-4 py-3 font-mono text-xs text-indigo-600 dark:text-indigo-400 whitespace-nowrap">{{ $req->request_number }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">{{ $req->created_at?->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                        <a href="{{ route('items', ['search' => $req->product_code_actual]) }}" target="_blank"
                                            class="hover:text-indigo-600 dark:hover:text-indigo-400 hover:underline">{{ $req->product_code_actual ?? '—' }} ↗</a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200 max-w-xs truncate" title="{{ $req->product_name_actual }}">{{ $req->product_name_actual }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300">{{ $userNames[$req->requested_by] ?? '—' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-300">{{ $req->gestor_id ? ($userNames[$req->gestor_id] ?? 'Asignado') : '—' }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $sm['cell'] }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $sm['dot'] }}"></span>{{ $sm['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200 tabular-nums">{{ $req->progress_done }} de {{ $req->progress_total }} ({{ $req->progress_percent }}%)</div>
                                        <div class="w-24 h-1.5 mx-auto mt-1 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-full bg-indigo-500" style="width: {{ $req->progress_percent }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">{{ $req->updated_at?->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <button wire:click="openDetail({{ $req->id }})" title="Ver solicitud"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">
                                        No hay solicitudes de video que coincidan con los filtros.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $requests->links() }}
                </div>
            </div>
        @endif
        @endunless
    </div>

    {{-- ═══════════════ MODAL: CREAR SOLICITUD ═══════════════ --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-[80] flex items-start justify-center p-4 overflow-y-auto"
            x-data @keydown.escape.window="$wire.closeCreateModal()">
            <div class="fixed inset-0 bg-black/50" wire:click="closeCreateModal"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-3xl mt-10 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $selectedItemId ? 'Nueva solicitud de video' : 'Solicitar video — Buscar producto' }}
                    </h3>
                    <button wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">&times;</button>
                </div>

                <div class="p-5 space-y-4">
                    @if (!$selectedItemId)
                        <div>
                            <input wire:model.live.debounce.300ms="productSearch" type="text" autofocus
                                placeholder="Buscar por código o descripción (mínimo 2 caracteres)…"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">

                            <div class="mt-2 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                <div class="max-h-72 overflow-y-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-900 text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 sticky top-0">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Código</th>
                                                <th class="px-3 py-2 text-left">Producto</th>
                                                <th class="px-3 py-2 text-left">Categoría</th>
                                                <th class="px-3 py-2 text-right">Stock</th>
                                                <th class="px-3 py-2"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                            @forelse ($productResults as $p)
                                                <tr class="hover:bg-indigo-50 dark:hover:bg-indigo-900/20">
                                                    <td class="px-3 py-2 font-bold text-gray-900 dark:text-white whitespace-nowrap">{{ $p->internal_code ?: $p->sku }}</td>
                                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $p->name }}</td>
                                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $p->category_name ?: '—' }}</td>
                                                    <td class="px-3 py-2 text-right tabular-nums text-gray-600 dark:text-gray-300">{{ (int) $p->total_stock }}</td>
                                                    <td class="px-3 py-2 text-right">
                                                        <button type="button" wire:click="selectProduct({{ $p->id }})"
                                                            class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-bold transition">
                                                            Seleccionar
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-3 py-4 text-xs text-gray-400 text-center">
                                                        {{ mb_strlen(trim($productSearch)) < 2 ? 'Escribe para buscar…' : 'Sin resultados.' }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @error('selectedItemId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div class="flex items-start justify-between gap-3 p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800">
                            <div>
                                <div class="text-[11px] uppercase font-bold text-indigo-500 dark:text-indigo-400">Producto seleccionado</div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $selectedItemLabel }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fecha de solicitud: {{ now()->format('d/m/Y') }} · Solicita: {{ auth()->user()->name }}</div>
                            </div>
                            <button type="button" wire:click="clearProduct" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline shrink-0">Cambiar</button>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Gestor de videos (opcional)</label>
                            <select wire:model="newGestorId"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                                <option value="">Asignar más tarde</option>
                                @foreach ($gestores as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Observaciones / instrucciones para el video</label>
                            <textarea wire:model="newInstructions" rows="4"
                                placeholder="Indica qué debe mostrarse, explicarse o demostrarse en el video…"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                            @error('newInstructions') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                    <button wire:click="closeCreateModal" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Cancelar</button>
                    <button wire:click="generateRequest" wire:loading.attr="disabled" @disabled(!$selectedItemId)
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-bold transition">
                        Generar solicitud
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════ DETALLE / LISTA DE CHEQUEO (pantalla completa) ═══════════════ --}}
    @if ($showDetail && $detail)
        @php $dsm = $statusMeta[$detail->status] ?? $statusMeta['pendiente']; @endphp
        <div class="max-w-[1600px] mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Solicitud {{ $detail->request_number }}</h2>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $dsm['cell'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $dsm['dot'] }}"></span>{{ $dsm['label'] }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            <span class="font-bold">{{ $detail->product_code_actual }}</span> · {{ $detail->product_name_actual }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="closeDetail"
                            class="inline-flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            ← Volver al listado
                        </button>
                        @if ($this->canEdit)
                            <button wire:click="saveDetail" wire:loading.attr="disabled"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-bold transition">
                                Guardar cambios
                            </button>
                        @endif
                    </div>
                </div>

                <div class="p-5 space-y-5">
                    <!-- Datos -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div>
                            <div class="text-gray-400 uppercase font-bold">Fecha solicitud</div>
                            <div class="text-gray-800 dark:text-gray-200">{{ $detail->created_at?->format('d/m/Y H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-gray-400 uppercase font-bold">Solicitado por</div>
                            <div class="text-gray-800 dark:text-gray-200">{{ $detailUserNames[$detail->requested_by] ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-400 uppercase font-bold">Última modificación</div>
                            <div class="text-gray-800 dark:text-gray-200">{{ $detail->updated_at?->format('d/m/Y H:i') }}{{ $detail->updated_by ? ' · ' . ($detailUserNames[$detail->updated_by] ?? '') : '' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-400 uppercase font-bold">Avance</div>
                            <div class="text-gray-800 dark:text-gray-200 font-bold tabular-nums">{{ $detail->progress_done }} de {{ $detail->progress_total }} — {{ $detail->progress_percent }}%</div>
                        </div>
                    </div>

                    <!-- Instrucciones -->
                    <div>
                        <div class="text-xs text-gray-400 uppercase font-bold mb-1">Instrucciones de Gerencia</div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap bg-gray-50 dark:bg-gray-900/40 rounded-lg p-3 border border-gray-100 dark:border-gray-700">{{ $detail->instructions ?: 'Sin instrucciones registradas.' }}</p>
                    </div>

                    <!-- Gestor -->
                    <div>
                        <label class="block text-xs text-gray-400 uppercase font-bold mb-1">Gestor de videos</label>
                        <select wire:model="detailGestorId" @disabled(!$this->canEdit)
                            class="block w-full sm:w-72 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 disabled:opacity-60">
                            <option value="">Sin asignar</option>
                            @foreach ($gestores as $g)
                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lista de chequeo -->
                    @php $detailTasks = $detail->tasks->keyBy('channel'); @endphp
                    <div>
                        <div class="text-xs text-gray-400 uppercase font-bold mb-2">Lista de chequeo</div>
                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                            <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900 text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    <tr>
                                        <th class="px-3 py-2 text-left w-8">#</th>
                                        <th class="px-3 py-2 text-left">Proceso</th>
                                        <th class="px-3 py-2 text-left w-32">Estado</th>
                                        <th class="px-3 py-2 text-left">Información / Enlace</th>
                                        <th class="px-3 py-2 text-left w-36">Última actualización</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($channels as $key => $cfg)
                                        @php
                                            $ts = $statusMeta[$taskInput[$key]['status'] ?? 'pendiente'] ?? $statusMeta['pendiente'];
                                            $t = $detailTasks->get($key);
                                        @endphp
                                        <tr>
                                            <td class="px-3 py-2 text-gray-400 align-top">{{ $cfg['order'] }}</td>
                                            <td class="px-3 py-2 font-semibold text-gray-800 dark:text-gray-100 align-top">{{ $cfg['label'] }}</td>
                                            <td class="px-3 py-2 align-top">
                                                @if (!$cfg['requires_link'])
                                                    <select wire:model="taskInput.{{ $key }}.status" @disabled(!$this->canEdit)
                                                        class="text-xs border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 py-1 pl-2 pr-6 text-gray-900 dark:text-white disabled:opacity-60">
                                                        <option value="pendiente">Pendiente</option>
                                                        <option value="en_proceso">En proceso</option>
                                                        <option value="listo">Listo</option>
                                                    </select>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $ts['cell'] }}">
                                                        <span class="w-1.5 h-1.5 rounded-full {{ $ts['dot'] }}"></span>{{ $ts['label'] }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 align-top">
                                                @if ($cfg['requires_link'])
                                                    <input type="url" wire:model="taskInput.{{ $key }}.link" @disabled(!$this->canEdit)
                                                        placeholder="Pegar enlace de {{ \Illuminate\Support\Str::of($cfg['label'])->after('subido a ') }}…"
                                                        class="block w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none disabled:opacity-60">
                                                    @error("taskInput.$key.link") <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                                                    @if (!empty($taskInput[$key]['link']))
                                                        <a href="{{ $taskInput[$key]['link'] }}" target="_blank" rel="noopener" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline">Abrir enlace ↗</a>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-400">Sin enlace</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 align-top text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                {{ $t && $t->updated_at && !$t->updated_at->eq($t->created_at) ? $t->updated_at->format('d/m/Y H:i') : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2">Los canales con enlace se marcan como “Listo” automáticamente al guardar un enlace válido. El enlace de YouTube se agrega a las Observaciones técnicas del producto.</p>
                    </div>

                    <!-- Historial del producto -->
                    @if ($detailHistory->isNotEmpty())
                        <div>
                            <div class="text-xs text-gray-400 uppercase font-bold mb-2">Solicitudes anteriores de este producto</div>
                            <div class="space-y-1">
                                @foreach ($detailHistory as $h)
                                    @php $hsm = $statusMeta[$h->status] ?? $statusMeta['pendiente']; @endphp
                                    <div class="flex items-center justify-between text-xs bg-gray-50 dark:bg-gray-900/40 rounded-md px-3 py-1.5">
                                        <span class="font-mono text-gray-600 dark:text-gray-300">{{ $h->request_number }}</span>
                                        <span class="text-gray-400">{{ $h->created_at?->format('d/m/Y') }}</span>
                                        <span>{{ $h->progress_done }}/{{ $h->progress_total }} — {{ $h->progress_percent }}%</span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold {{ $hsm['cell'] }}">{{ $hsm['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Trazabilidad -->
                    @if ($detail->logs->isNotEmpty())
                        <details class="text-xs">
                            <summary class="cursor-pointer text-gray-400 uppercase font-bold">Trazabilidad ({{ $detail->logs->count() }})</summary>
                            <div class="mt-2 space-y-1 max-h-48 overflow-y-auto">
                                @foreach ($detail->logs as $log)
                                    <div class="flex items-start gap-2 text-gray-500 dark:text-gray-400">
                                        <span class="text-gray-400 shrink-0">{{ $log->created_at?->format('d/m H:i') }}</span>
                                        <span class="shrink-0 font-semibold text-gray-600 dark:text-gray-300">{{ $detailUserNames[$log->user_id] ?? 'Sistema' }}</span>
                                        <span>{{ $log->action }}{{ $log->channel ? " · {$log->channel}" : '' }}{{ $log->new_value ? " → {$log->new_value}" : '' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('items', ['search' => $detail->product_code_actual]) }}" target="_blank"
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Abrir producto en inventario ↗</a>
                    <div class="flex items-center gap-2">
                        <button wire:click="closeDetail" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Volver al listado</button>
                        @if ($this->canEdit)
                            <button wire:click="saveDetail" wire:loading.attr="disabled"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-bold transition">
                                Guardar cambios
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
