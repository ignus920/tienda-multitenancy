<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Encabezado y Estadísticas -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-gray-200 dark:border-gray-700">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Proyectos de Iluminación</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gestiona tus cotizaciones, órdenes de producción y comunicación técnica desde un solo lugar.</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Botón Crear Proyecto -->
            <button wire:click="openCreateModal"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-lg shadow transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Crear Proyecto
            </button>
        </div>
    </div>

    <!-- Contenido Principal: Listado y Panel de Pendientes -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mt-6">
        
        <!-- Columna Izquierda: Buscador, Pestañas y Listado de Proyectos -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Filtros y Buscador -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row gap-4 justify-between items-center">
                <!-- Buscador multi-palabra -->
                <div class="relative w-full md:max-w-md">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="search" 
                        placeholder="Buscar por título, cliente, comercial..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <!-- Pestañas (Tabs) -->
                <div class="flex bg-gray-100 dark:bg-gray-900 p-1 rounded-lg text-xs font-semibold self-stretch md:self-auto">
                    <button wire:click="$set('selectedTab', 'activos')" 
                        class="px-4 py-1.5 rounded-md transition-colors {{ $selectedTab === 'activos' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                        Activos
                    </button>
                    <button wire:click="$set('selectedTab', 'archivados')" 
                        class="px-4 py-1.5 rounded-md transition-colors {{ $selectedTab === 'archivados' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
                        Historial Archivados
                    </button>
                </div>

                <!-- Filtrar por Estado -->
                <div class="w-full md:w-auto">
                    <select wire:model.live="selectedStatus" 
                        class="block w-full py-2 pl-3 pr-10 border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="">Todos los estados</option>
                        <option value="cotizacion">En Cotización</option>
                        <option value="negociacion">En Negociación</option>
                        <option value="orden_creada">Orden Creada</option>
                        <option value="en_produccion">En Producción</option>
                        <option value="terminado">Terminado / Listo</option>
                    </select>
                </div>
            </div>

            <!-- Proyectos Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse($projects as $project)
                    @php
                        $statusColors = [
                            'cotizacion' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            'negociacion' => 'bg-pink-50 text-pink-700 dark:bg-pink-900/30 dark:text-pink-400',
                            'orden_creada' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                            'en_produccion' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 animate-pulse',
                            'terminado' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                            'archivados' => 'bg-gray-100 text-gray-700 dark:bg-gray-900/40 dark:text-gray-400'
                        ];
                        $statusNames = [
                            'cotizacion' => 'Cotización',
                            'negociacion' => 'Negociación',
                            'orden_creada' => 'Orden Creada',
                            'en_produccion' => 'En Producción',
                            'terminado' => 'Terminado',
                            'archivados' => 'Archivado'
                        ];
                    @endphp

                    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all flex flex-col justify-between h-56 group relative">
                        <div>
                            <!-- Header de la tarjeta -->
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <span class="px-2 py-0.5 text-2xs font-bold rounded-full {{ $statusColors[$project->status] ?? 'bg-gray-50' }}">
                                    {{ $statusNames[$project->status] ?? $project->status }}
                                </span>
                                @if($project->questions_count > 0)
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-2xs font-semibold bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400" title="Tiene preguntas pendientes para el cliente">
                                        <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        {{ $project->questions_count }}
                                    </span>
                                @endif
                            </div>

                            <!-- Título del proyecto -->
                            <a href="{{ route('tenant.projects.workspace', ['id' => $project->id]) }}" class="block">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 line-clamp-1 mb-1">{{ $project->title }}</h3>
                            </a>

                            <!-- Cliente y Asesor -->
                            <p class="text-xs font-semibold text-indigo-500 dark:text-indigo-400 truncate mb-1">
                                {{ $project->customer->businessName ?? trim(($project->customer->firstName ?? '') . ' ' . ($project->customer->lastName ?? '')) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3">
                                {{ $project->description }}
                            </p>
                        </div>

                        <!-- Footer de la tarjeta -->
                        <div class="flex items-center justify-between border-t border-gray-100 dark:border-gray-700 pt-3 text-2xs text-gray-400">
                            <span class="truncate">Comercial: {{ $project->creator->name ?? 'N/A' }}</span>
                            <span>{{ $project->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-gray-800 rounded-xl p-12 text-center border border-dashed border-gray-200 dark:border-gray-700">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No se encontraron proyectos</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Comienza creando un proyecto de iluminación para cotizar.</p>
                    </div>
                @endforelse
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                {{ $projects->links() }}
            </div>
        </div>

        <!-- Columna Derecha: Panel de Pendientes de Usuario -->
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-indigo-50 to-white dark:from-gray-850 dark:to-gray-800 rounded-xl p-5 shadow-sm border border-indigo-100 dark:border-gray-700">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Mis Pendientes
                </h2>

                <!-- Alertas de Menciones -->
                <div class="space-y-3">
                    <h3 class="text-2xs font-bold text-gray-400 tracking-wider uppercase">Menciones en Chats (@)</h3>
                    @forelse($myMentions as $mention)
                        <div class="bg-white dark:bg-gray-850 rounded-lg p-3 border border-gray-100 dark:border-gray-750 shadow-2xs relative flex flex-col justify-between gap-1.5">
                            <div class="flex items-center justify-between text-2xs">
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $mention->sender->name }}</span>
                                <span class="text-gray-400">{{ $mention->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 italic line-clamp-2">
                                "{{ $mention->message->message }}"
                            </p>
                            <div class="flex items-center justify-between border-t border-gray-50 dark:border-gray-750 pt-2 mt-1">
                                <span class="text-3xs text-gray-400 truncate max-w-[120px]" title="{{ $mention->project->title }}">{{ $mention->project->title }}</span>
                                <button wire:click="markNotificationAsSeen({{ $mention->id }})"
                                    class="text-xs font-semibold text-indigo-500 hover:text-indigo-600 flex items-center gap-0.5">
                                    Ver mensaje
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-2">No tienes menciones nuevas.</p>
                    @endforelse

                    <!-- Alertas de Preguntas al Cliente por Responder -->
                    <h3 class="text-2xs font-bold text-gray-400 tracking-wider uppercase mt-4">Preguntas por Responder</h3>
                    @forelse($myQuestions as $question)
                        <div class="bg-white dark:bg-gray-850 rounded-lg p-3 border border-gray-100 dark:border-gray-750 shadow-2xs">
                            <div class="flex items-center justify-between text-2xs mb-1">
                                <span class="font-bold text-amber-600 dark:text-amber-400">Pregunta de: {{ $question->asker->name }}</span>
                                <span class="text-gray-400">{{ $question->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2 mb-2">
                                "{{ $question->question }}"
                            </p>
                            <div class="flex items-center justify-between border-t border-gray-50 dark:border-gray-750 pt-2">
                                <span class="text-3xs text-gray-400 truncate max-w-[120px]" title="{{ $question->project->title }}">{{ $question->project->title }}</span>
                                <a href="{{ route('tenant.projects.workspace', ['id' => $question->project_id]) }}"
                                    class="text-xs font-semibold text-amber-500 hover:text-amber-600 flex items-center gap-0.5">
                                    Responder
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-2">No tienes preguntas técnicas pendientes.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Crear Proyecto -->
    @if($showCreateModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-lg w-full overflow-hidden">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Nuevo Proyecto de Iluminación</h3>
                <button wire:click="$set('showCreateModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4">
                <!-- Título -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Título del Proyecto *</label>
                    <input wire:model="title" type="text" placeholder="Ej: Luminarias Fachada Hotel Estelar"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('title') <span class="text-xs text-red-500 mt-0.5 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <!-- Buscador de Cliente (Autocompletar) -->
                <div class="relative" x-data="{ open: true }" @click.away="open = false">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Cliente *</label>
                    
                    @if($selectedCustomerId)
                        <div class="flex items-center justify-between bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-800 rounded-lg p-2 text-sm text-indigo-700 dark:text-indigo-400">
                            <span class="font-semibold">{{ $selectedCustomerName }}</span>
                            <button type="button" wire:click="clearCustomerSelection" class="text-indigo-500 hover:text-indigo-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @else
                        <input wire:model.live.debounce.300ms="customerSearch" @focus="open = true" type="text" placeholder="Escribe NIT o nombre comercial..."
                            class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        
                        @if(!empty($customerResults) && $customerSearch)
                            <div x-show="open" class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-48 overflow-y-auto">
                                @foreach($customerResults as $res)
                                    <button type="button" wire:click="selectCustomer({{ $res['id'] }}, '{{ addslashes($res['name']) }}')"
                                        class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-750 flex items-center justify-between">
                                        <span class="font-bold">{{ $res['name'] }}</span>
                                        <span class="text-gray-400">{{ $res['identification'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif
                    @error('selectedCustomerId') <span class="text-xs text-red-500 mt-0.5 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <!-- Descripción -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Descripción del Proyecto *</label>
                    <textarea wire:model="description" rows="4" placeholder="Describe los requerimientos mínimos de iluminación, drivers, perfiles..."
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
                    @error('description') <span class="text-xs text-red-500 mt-0.5 block font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                <button wire:click="$set('showCreateModal', false)" type="button"
                    class="px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Cancelar
                </button>
                <button wire:click="createProject" type="button"
                    class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 rounded-lg shadow transition-colors">
                    Iniciar Proyecto
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
