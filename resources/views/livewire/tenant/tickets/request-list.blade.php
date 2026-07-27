<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-12xl mx-auto">
        <!-- Mensajes de Alerta Globales -->
        @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-lg shadow-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="show = false" class="inline-flex text-green-500 hover:text-green-700 focus:outline-none">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif

        @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-lg shadow-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="show = false" class="inline-flex text-red-500 hover:text-red-700 focus:outline-none">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endif

        <!-- Header -->
        @if(!$isSupplier)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $isSupplier ? 'Requests Panel' : 'Panel de Solicitudes' }}
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            {{ $isSupplier ? 'Management and tracking of requests' : 'Gestión y seguimiento de requerimientos' }}
                        </p>
                    </div>
                    @if($isSupplier)
                        <a href="{{ route('imports.imports-orders') }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002-2h2a2 2 0 012 2"></path>
                            </svg>
                            Orders
                        </a>
                    @endif
                </div>
                @if(!$isSupplier)
                <div class="flex items-center gap-3">
                    <a href="{{ route('tenant.tickets.departments') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Gestionar Departamentos
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        @php
            $statusTranslations = [
                'global' => 'Global',
                'registrado' => 'Registered',
                'reactivado' => 'Reactivated',
                'solucionado' => 'Solved',
                'imposibilidad' => 'Impossibility',
                'abierto' => 'Open',
                'cerrado' => 'Closed',
                'en proceso' => 'In Progress',
            ];
        @endphp

        <!-- KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <button wire:click="filterByStatus(null)"
                class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all group {{ is_null($selectedStatus) ? 'ring-2 ring-indigo-500 border-transparent' : '' }}">
                <div class="h-12 w-12 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                </div>
                <div class="ml-4 text-left">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Global</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $totalRequests }}</p>
                </div>
            </button>

            @foreach($statuses as $status)
            <button wire:click="filterByStatus({{ $status->id }})"
                class="flex items-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all group {{ $selectedStatus == $status->id ? 'ring-2 ring-indigo-500 border-transparent' : '' }}">
                <div class="h-12 w-12 flex items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-900/50 transition-colors" style="color: var(--tw-color-{{ $status->color }}-600)">
                    <x-dynamic-component :component="$status->icon" class="w-6 h-6"/>
                </div>
                <div class="ml-4 text-left">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $isSupplier ? ($statusTranslations[strtolower($status->name)] ?? $status->name) : $status->name }}
                    </p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $allStats[$status->id] ?? 0 }}</p>
                </div>
            </button>
            @endforeach
        </div>

        <!-- DataTable Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <!-- Filtros + Toolbar -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <!-- Fila 1: Filtros -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-end mb-4">
                    @if(!$isSupplier)
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Departamento</label>
                        <select wire:model.live="departmentId"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos los Departamentos</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Proveedor</label>
                        <select wire:model.live="supplierIdFilter"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos los Proveedores</option>
                            @foreach($suppliersList as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif                     <div class="{{ $isSupplier ? 'lg:col-span-3' : 'lg:col-span-1' }} grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                {{ $isSupplier ? 'From' : 'Desde' }}
                            </label>
                            <input type="date" wire:model.live="dateFrom"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                {{ $isSupplier ? 'To' : 'Hasta' }}
                            </label>
                            <input type="date" wire:model.live="dateTo"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" wire:model.live="search" placeholder="{{ $isSupplier ? 'Search records...' : 'Buscar registros...' }}"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                </div>
                <!-- Fila 2: Registros + Exportación -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $isSupplier ? 'Show:' : 'Mostrar:' }}
                        </label>
                        <select wire:model.live="perPage"
                            class="border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <x-export-buttons />
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36">
                                {{ $isSupplier ? 'Date/Time' : 'Fecha/Hora' }}
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $isSupplier ? 'Request / Product' : 'Solicitud / Producto' }}
                            </th>
                            @if(!$isSupplier)
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Depto. / Prov.</th>
                            @endif
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $isSupplier ? 'Status' : 'Estado' }}
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $isSupplier ? 'Actions' : 'Acciones' }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        @forelse($requests as $req)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">{{ $req->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 leading-tight">
                                {{ $req->created_at->format('d M, Y') }}<br>
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $req->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-3">
                                    @if($req->product)
                                        @php
                                            $imageName = $req->product->image_name ?? $req->product->image;
                                            $imgUrl = $imageName 
                                                ? 'https://cloud.ticsia.com/fervicom/storage/items/' . $imageName
                                                : null;
                                        @endphp
                                        @if($imgUrl)
                                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0 border border-gray-200 dark:border-gray-600">
                                                <img class="w-full h-full object-cover" src="{{ $imgUrl }}" alt="{{ $req->product->name }}">
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs flex-shrink-0 border border-indigo-100 dark:border-indigo-900/50">
                                                {{ strtoupper(substr($req->product->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    @endif
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white leading-tight uppercase flex items-center gap-1.5 flex-wrap">
                                            @if($req->is_reactivated)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-wider animate-pulse gap-1">
                                                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    {{ $isSupplier ? 'New Request' : 'Nueva Solicitud' }}
                                                </span>
                                            @endif
                                            @if($req->product)
                                                <span class="font-mono text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded mr-1">
                                                    {{ $req->product->internal_code ?: ($isSupplier ? 'NO CODE' : 'SIN COD') }}
                                                </span>
                                                — {{ $req->product->name }}
                                            @else
                                                SOLICITUD SIN PRODUCTO
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400 mt-1 leading-relaxed font-medium">
                                            {{ strip_tags($req->detail) }}
                                        </div>
                                        <div class="text-xs text-indigo-500 dark:text-indigo-400 font-medium mt-1 uppercase">
                                            {{ $isSupplier ? 'FROM' : 'DE' }}: {{ $req->creator->name ?? ($isSupplier ? 'SYSTEM USER' : 'USUARIO SISTEMA') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @if(!$isSupplier)
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 uppercase">
                                    @if($req->supplier_id)
                                        {{ $req->supplier?->name ?? 'Sin Proveedor' }}
                                    @else
                                        {{ $req->department?->name ?? 'Sin Dept.' }}
                                    @endif
                                </span>
                            </td>
                            @endif
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white uppercase"
                                    style="background-color: var(--tw-color-{{ $req->status->color }}-500, #{{ $req->status->color == 'indigo' ? '6366f1' : ($req->status->color == 'green' ? '22c55e' : ($req->status->color == 'blue' ? '3b82f6' : 'ef4444')) }})">
                                    {{ $isSupplier ? ($statusTranslations[strtolower($req->status->name)] ?? $req->status->name) : $req->status->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <button @click.stop="$dispatch('viewTicket', { id: {{ $req->id }} })" 
                                            class="p-1.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-gray-400 dark:text-gray-500 italic font-medium">
                                        {{ $isSupplier ? 'No requests found.' : 'No se encontraron solicitudes.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                {{ $requests->links() }}
            </div>
            @endif
        </div>
    </div>
    @push('scripts')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    @endpush
</div>
