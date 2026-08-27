<div class="py-4 w-full px-4 sm:px-6" x-data="{ 
    scrollToBottom() {
        const container = this.$refs.chatContainer;
        if(container) {
            this.$nextTick(() => {
                container.scrollTop = container.scrollHeight;
            });
        }
    }
}" x-init="scrollToBottom()" @message-sent.window="scrollToBottom()">

    <!-- Header del Espacio de Trabajo -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('tenant.projects') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a Proyectos
                </a>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                {{ $project->title }}
                @if($project->vencimiento_status === 'vencido')
                    <span class="px-2 py-0.5 text-2xs font-bold rounded-full bg-red-600 text-white">Vencido</span>
                @elseif($project->vencimiento_status === 'proximo_vencer')
                    <span class="px-2 py-0.5 text-2xs font-bold rounded-full bg-amber-500 text-white">Próximo a vencer</span>
                @endif
            </h1>
            <p class="text-xs text-indigo-600 dark:text-indigo-400 font-bold mt-0.5">
                @if($project->type === 'internal')
                    Dirigido a: {{ $project->assignedUser->name ?? 'N/A' }}
                @else
                    Cliente: {{ $project->customer->businessName ?? trim(($project->customer->firstName ?? '') . ' ' . ($project->customer->lastName ?? '')) }}
                @endif
            </p>
        </div>
        <!-- Selector de Estado Visual (Pipeline) -->
        <div class="flex items-center gap-2">
            @php
                $statuses = $project->type === 'internal'
                    ? ['cotizacion' => 'Creado', 'en_produccion' => 'En Desarrollo', 'terminado' => 'Terminado', 'cerrado_entregado' => 'Finalizado']
                    : ['cotizacion' => 'Cotización', 'negociacion' => 'Negociación', 'orden_creada' => 'Orden Creada', 'en_produccion' => 'En Producción', 'terminado' => 'Terminado', 'cerrado_entregado' => 'Finalizado / Entregado'];
            @endphp
            <div class="flex flex-wrap gap-1 bg-gray-100 dark:bg-gray-900 p-1 rounded-lg text-2xs font-semibold">
                @foreach($statuses as $key => $name)
                    <span class="px-2.5 py-1 rounded {{ $project->status === $key ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-500' }}">
                        {{ $name }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Barra de Pestañas -->
    <div class="flex bg-gray-100 dark:bg-gray-900 p-1 rounded-lg text-xs font-semibold mb-6 w-fit">
        <button wire:click="$set('activeTab', 'chat')"
            class="px-4 py-1.5 rounded-md transition-colors {{ $activeTab === 'chat' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
            Chat
        </button>
        <button wire:click="$set('activeTab', 'materiales')"
            class="px-4 py-1.5 rounded-md transition-colors {{ $activeTab === 'materiales' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
            Materiales
        </button>
        <button wire:click="$set('activeTab', 'participantes')"
            class="px-4 py-1.5 rounded-md transition-colors {{ $activeTab === 'participantes' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
            Participantes
        </button>
        <button wire:click="$set('activeTab', 'archivos')"
            class="px-4 py-1.5 rounded-md transition-colors {{ $activeTab === 'archivos' ? 'bg-white dark:bg-gray-800 text-indigo-600 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}">
            Archivos
        </button>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Columna Izquierda: Información de Orden, Avances y Preguntas -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Ficha Técnica y Acciones de Estado -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wider">Estado e Información</h2>
                
                <div class="space-y-4 text-xs">
                    <div>
                        <span class="text-gray-400 block">Descripción inicial:</span>
                        <p class="text-gray-700 dark:text-gray-300 font-medium whitespace-pre-wrap mt-0.5">{{ $project->description }}</p>
                    </div>

                    <!-- Datos de Orden de Producción -->
                    @if($project->orders->count() > 0 || $project->qty)
                        <div class="bg-gray-50 dark:bg-gray-850 p-3 rounded-lg border border-gray-100 dark:border-gray-750">
                            <div class="text-2xs font-bold text-indigo-600 dark:text-indigo-400 uppercase border-b border-indigo-100 dark:border-indigo-900 pb-1 mb-2">
                                Orden de Pedido / Producción
                            </div>
                            
                            @if($project->orders->count() > 0)
                                <!-- Nueva lógica: Múltiples ítems -->
                                <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded mb-3">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-100 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-2 py-1.5 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Cant.</th>
                                                <th scope="col" class="px-2 py-1.5 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                                                <th scope="col" class="px-2 py-1.5 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Total</th>
                                                <th scope="col" class="px-2 py-1.5 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Obs.</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($project->orders as $orderItem)
                                            <tr>
                                                <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900 dark:text-gray-100">{{ $orderItem->qty }}</td>
                                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">${{ number_format($orderItem->price_unit, 2) }}</td>
                                                <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-900 dark:text-gray-100 font-bold">${{ number_format($orderItem->total_value, 2) }}</td>
                                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 truncate max-w-[120px]" title="{{ $orderItem->observations }}">{{ $orderItem->observations ?: '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <!-- Lógica antigua: Ítem único -->
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <span class="text-gray-400 block text-[10px] uppercase">Cantidad:</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $project->qty }} unidades</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 block text-[10px] uppercase">Precio Unit:</span>
                                        <span class="font-bold text-gray-900 dark:text-white">${{ number_format($project->price_unit, 2) }}</span>
                                    </div>
                                    @if($project->prod_observations)
                                    <div class="col-span-2">
                                        <span class="text-gray-400 block text-[10px] uppercase">Observaciones:</span>
                                        <p class="text-gray-700 dark:text-gray-300 mt-0.5">{{ $project->prod_observations }}</p>
                                    </div>
                                    @endif
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-3 mt-2 border-t border-gray-200 dark:border-gray-700 pt-2">
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Valor Total Global:</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">${{ number_format($project->total_value, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 block text-[10px] uppercase">Fecha Entrega:</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $project->delivery_date ? $project->delivery_date->format('d/m/Y') : 'No establecida' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Avances de Laboratorio Cierre -->
                    @if($project->completion_date)
                        <div class="bg-emerald-50/50 dark:bg-emerald-950/20 p-3 rounded-lg border border-emerald-100 dark:border-emerald-900 grid grid-cols-1 gap-1">
                            <div class="text-2xs font-bold text-emerald-600 dark:text-emerald-400 uppercase border-b border-emerald-100 dark:border-emerald-900 pb-1">
                                Cierre de Laboratorio
                            </div>
                            <div>
                                <span class="text-gray-400 block">Terminado el:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $project->completion_date->format('d/m/Y') }}</span>
                            </div>
                            @if($project->lab_observations)
                                <div>
                                    <span class="text-gray-400 block">Notas Laboratorio:</span>
                                    <p class="text-gray-700 dark:text-gray-300">{{ $project->lab_observations }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Acciones según rol y estado -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-col gap-2">
                        @if($project->type === 'external')
                            <!-- Comercial marca en negociación -->
                            @if($project->status === 'cotizacion')
                                <button wire:click="markNegotiation"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg font-bold shadow-sm transition-colors">
                                    Marcar en Negociación
                                </button>
                            @endif

                            <!-- Comercial genera Orden (Solo el creador del proyecto) -->
                            @if(in_array($project->status, ['cotizacion', 'negociacion']) && Auth::id() === $project->created_by)
                                <button wire:click="$set('showOrderModal', true)"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-sm transition-colors">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    Crear Orden de Pedido
                                </button>
                            @endif

                            <!-- Comercial / Laboratorio inicia producción -->
                            @if($project->status === 'orden_creada')
                                <button wire:click="startProduction"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-bold shadow-sm transition-colors">
                                    Iniciar Producción (Fábrica)
                                </button>
                            @endif
                        @else
                            <!-- Área responsable inicia desarrollo (proyecto interno) -->
                            @if($project->status === 'cotizacion')
                                <button wire:click="$set('showStartDevelopmentModal', true)"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-bold shadow-sm transition-colors">
                                    Iniciar Desarrollo
                                </button>
                            @endif
                        @endif

                        <!-- Se agregan avances y preguntas durante el desarrollo/producción -->
                        @if($project->status === 'en_produccion')
                            <div class="flex flex-col sm:flex-row gap-2">
                                <button wire:click="openAdvanceModal"
                                    class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 text-gray-800 dark:text-white rounded-lg font-bold shadow-2xs transition-colors">
                                    Registrar Avance Técnico
                                </button>

                                <button wire:click="$set('showNoveltyModal', true)"
                                    class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow-sm transition-colors">
                                    Novedades del cliente
                                </button>

                                @if($project->type === 'external')
                                    <button wire:click="$set('showQuestionModal', true)"
                                        class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold shadow-sm transition-colors">
                                        Generar Pregunta al Asesor/Cliente
                                    </button>
                                @endif

                                <button wire:click="$set('showLabFinishModal', true)"
                                    class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow-sm transition-colors">
                                    {{ $project->type === 'internal' ? 'Marcar como Terminado' : 'Terminar Producción' }}
                                </button>
                            </div>
                        @endif

                        <!-- Cierra el caso: entrega (externo) o finalización (interno) -->
                        @if($project->status === 'terminado')
                            <div class="bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300 p-2.5 rounded-lg text-center font-semibold mb-2">
                                @if($project->type === 'internal')
                                    ¡Trabajo Terminado! Listo para que el solicitante lo verifique y finalice.
                                @else
                                    ¡Producción Terminada! Listo para entregar al cliente.
                                @endif
                            </div>
                            <button wire:click="$set('showCloseModal', true)"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-sm transition-colors">
                                {{ $project->type === 'internal' ? 'Finalizar Proyecto' : 'Registrar Entrega' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Panel de Preguntas Pendientes para el Cliente -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wider flex items-center justify-between">
                    <span>Preguntas Cliente</span>
                    <span class="px-2 py-0.5 text-3xs font-extrabold rounded-full bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300">
                        {{ count($questions->where('status', 'pendiente')) }} Pendientes
                    </span>
                </h2>

                <div class="space-y-4">
                    @forelse($questions as $q)
                        <div class="p-3 rounded-lg border text-xs {{ $q->status === 'pendiente' ? 'bg-red-50/50 dark:bg-red-950/10 border-red-100 dark:border-red-900/50' : 'bg-gray-50 dark:bg-gray-850 border-gray-200 dark:border-gray-750' }}">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold text-gray-500">Pregunta de {{ $q->asker->name }}</span>
                                <span class="text-gray-400 text-3xs">{{ $q->created_at->format('d/m H:i') }}</span>
                            </div>
                            <p class="text-gray-800 dark:text-gray-200 font-semibold mb-2">{{ $q->question }}</p>
                            
                            @if($q->answer)
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-bold text-indigo-600 dark:text-indigo-400">Respuesta de {{ $q->answerer->name ?? 'Comercial' }}</span>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-300 italic">"{{ $q->answer }}"</p>
                                </div>
                            @endif

                            <div class="mt-2.5 flex justify-end gap-2 pt-1.5 border-t border-gray-100 dark:border-gray-700">
                                @if($q->status === 'pendiente')
                                    <button wire:click="openAnswerModal({{ $q->id }})"
                                        class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded font-bold text-2xs transition-colors">
                                        Responder
                                    </button>
                                @elseif($q->status === 'respondida')
                                    <button wire:click="closeQuestion({{ $q->id }})"
                                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-bold text-2xs transition-colors">
                                        Marcar como Resuelta
                                    </button>
                                @else
                                    <span class="text-2xs font-extrabold text-gray-400 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg> Resuelta
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-4">No se han registrado preguntas.</p>
                    @endforelse
                </div>
            </div>

            <!-- Bitácora de Avances de Laboratorio -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wider">Avances Técnicos</h2>
                
                <div class="space-y-4 max-h-[300px] overflow-y-auto pr-1">
                    @forelse($advances as $adv)
                        <div class="relative pl-4 border-l-2 border-indigo-500 text-xs">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $adv->user->name }} ({{ $adv->percentage }}%)</span>
                                <span class="text-gray-400 text-3xs">{{ $adv->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 font-medium">{{ $adv->description }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-4">No se han registrado avances.</p>
                    @endforelse
                </div>
            </div>

            <!-- Historial de Estados -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Historial de Estados</h2>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-cloak class="space-y-3 max-h-[260px] overflow-y-auto pr-1 mt-4">
                    @forelse($project->statusHistory as $entry)
                        <div class="relative pl-4 border-l-2 border-gray-300 dark:border-gray-600 text-xs">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $entry->from_status ?? 'Creado' }} → {{ $entry->to_status }}</span>
                                <span class="text-gray-400 text-3xs">{{ $entry->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">{{ $entry->user->name ?? 'Usuario' }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-4">Sin cambios de estado registrados.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Columna Derecha: contenido según pestaña activa -->
        @if($activeTab === 'chat')
        <!-- Chat Interactivo estilo WhatsApp -->
        <div class="lg:col-span-2 flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden" style="height: calc(100vh - 180px);">
            <!-- Header del Chat (estilo WhatsApp) -->
            <div class="px-4 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 dark:from-indigo-700 dark:to-indigo-800 flex items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Foro del Proyecto</h3>
                        <p class="text-xs text-indigo-100">{{ count($messages) }} mensajes • {{ count($usersList) }} participantes</p>
                    </div>
                </div>
                
                <!-- Filtros del chat -->
                <div class="flex items-center gap-2">
                    <select wire:model.live="chatFilterUser" 
                        class="py-2 px-3 bg-white border border-gray-200 text-gray-700 rounded-lg text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-300 shadow-sm">
                        <option value="">👤 Todos los usuarios</option>
                        @foreach($usersList as $u)
                            <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="chatFilterRole" 
                        class="py-2 px-3 bg-white border border-gray-200 text-gray-700 rounded-lg text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-300 shadow-sm">
                        <option value="">🏷️ Todos los roles</option>
                        <option value="2">Comercial</option>
                        <option value="8">Laboratorio</option>
                        <option value="1">Gerencia / Admin</option>
                    </select>
                </div>
            </div>

            <!-- Contenedor del Chat (Mensajes) con fondo estilo WhatsApp -->
            <div x-ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#e5ddd5] dark:bg-gray-900 bg-blend-multiply" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;200&quot; height=&quot;200&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cdefs%3E%3Cpattern id=&quot;p&quot; width=&quot;40&quot; height=&quot;40&quot; patternUnits=&quot;userSpaceOnUse&quot;%3E%3Ccircle cx=&quot;20&quot; cy=&quot;20&quot; r=&quot;1.5&quot; fill=&quot;%23ccc4b8&quot; opacity=&quot;0.3&quot;/%3E%3C/pattern%3E%3C/defs%3E%3Crect width=&quot;200&quot; height=&quot;200&quot; fill=&quot;url(%23p)&quot;/%3E%3C/svg%3E');">
                @forelse($messages as $msg)
                    @php
                        $isMe = $msg->user_id === Auth::id();
                        $roleColors = [
                            2 => ['name' => 'text-indigo-700', 'bg' => 'bg-indigo-100', 'avatar' => 'bg-indigo-500'],
                            8 => ['name' => 'text-pink-700', 'bg' => 'bg-pink-100', 'avatar' => 'bg-pink-500'],
                            1 => ['name' => 'text-emerald-700', 'bg' => 'bg-emerald-100', 'avatar' => 'bg-emerald-500'],
                        ];
                        $roleStyle = $roleColors[$msg->user->profile_id] ?? ['name' => 'text-gray-700', 'bg' => 'bg-gray-100', 'avatar' => 'bg-gray-500'];
                        $initials = strtoupper(substr($msg->user->name, 0, 2));
                    @endphp

                    <!-- Burbuja de mensaje -->
                    <div id="msg-{{ $msg->id }}" class="flex {{ $isMe ? 'justify-end' : 'justify-start' }} group"
                        x-data="{
                            editing: false,
                            editText: @js($msg->message),
                            canEdit: {{ $isMe ? 'true' : 'false' }}
                        }"
                        @if($isMe)
                        x-init="setTimeout(() => canEdit = false, {{ max(0, (10 - now()->diffInSeconds($msg->created_at)) * 1000) }})"
                        @endif>
                        <!-- Avatar (solo otros) -->
                        @if(!$isMe)
                            <div class="w-8 h-8 rounded-full {{ $roleStyle['avatar'] }} flex items-center justify-center text-white text-3xs font-bold mr-2 mt-auto shrink-0 shadow-sm">
                                {{ $initials }}
                            </div>
                        @endif

                        <div class="max-w-[75%] relative">
                            <div class="rounded-xl px-3.5 py-2.5 shadow-sm {{ $isMe ? 'bg-green-100 dark:bg-green-800 rounded-br-sm' : 'bg-white dark:bg-gray-700 rounded-bl-sm' }}">
                                
                                <!-- Remitente (solo en mensajes de otros) -->
                                @if(!$isMe)
                                    <div class="text-sm font-extrabold {{ $roleStyle['name'] }} mb-0.5 flex items-center gap-1.5">
                                        {{ $msg->user->name }}
                                        <span class="text-gray-400 font-normal text-[10px]">• {{ $msg->user->profile->name ?? 'Usuario' }}</span>
                                    </div>
                                @endif

                                <!-- Cita / Respuesta a un mensaje anterior -->
                                @if($msg->repliedTo)
                                    <div @click="
                                        const el = document.getElementById('msg-{{ $msg->reply_to_id }}');
                                        if(el) {
                                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                            el.classList.add('ring-2', 'ring-amber-400', 'rounded-xl');
                                            setTimeout(() => el.classList.remove('ring-2', 'ring-amber-400', 'rounded-xl'), 2500);
                                        }
                                    " class="mb-2 p-2 rounded-lg bg-black/5 dark:bg-white/5 border-l-3 border-indigo-400 text-3xs cursor-pointer hover:bg-black/8 dark:hover:bg-white/10 transition-colors">
                                        <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $msg->repliedTo->user->name }}</span>
                                        <p class="truncate text-gray-600 dark:text-gray-300 italic mt-0.5">"{{ Str::limit($msg->repliedTo->message, 80) }}"</p>
                                    </div>
                                @endif

                                <!-- Contenido del mensaje -->
                                <div x-show="!editing">
                                    @if(trim($msg->message ?? ''))
                                        <p class="text-xs leading-relaxed break-words text-gray-800 dark:text-gray-100">
                                            {!! preg_replace('/(@[a-zA-Z0-9_\-\.]+)/', '<span class="font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900 px-0.5 rounded">$1</span>', e($msg->message)) !!}
                                        </p>
                                    @endif

                                    <!-- Adjuntos del mensaje (solo dentro de esta burbuja, sin mosaico) -->
                                    @if($msg->files->isNotEmpty())
                                        <div class="flex flex-col gap-1.5 {{ trim($msg->message ?? '') ? 'mt-2' : '' }}">
                                            @foreach($msg->files as $file)
                                                <a href="{{ Storage::url($file->file_path) }}" target="_blank"
                                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-black/5 dark:bg-white/5 hover:bg-black/10 dark:hover:bg-white/10 transition-colors text-2xs">
                                                    @if($file->is_image)
                                                        <x-heroicon-o-photo class="w-4 h-4 text-indigo-500 shrink-0" />
                                                    @elseif(in_array($file->file_type, ['xls', 'xlsx']))
                                                        <x-heroicon-o-table-cells class="w-4 h-4 text-emerald-500 shrink-0" />
                                                    @elseif($file->file_type === 'pdf')
                                                        <x-heroicon-o-document-text class="w-4 h-4 text-red-500 shrink-0" />
                                                    @else
                                                        <x-heroicon-o-document class="w-4 h-4 text-indigo-500 shrink-0" />
                                                    @endif
                                                    <span class="truncate text-gray-700 dark:text-gray-200 font-medium">{{ $file->file_name }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if($isMe)
                                    <div x-show="editing" x-cloak class="space-y-1.5">
                                        <textarea x-model="editText" rows="2"
                                            class="w-full text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-1.5 focus:ring-1 focus:ring-indigo-500 focus:outline-none"></textarea>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="editing = false" class="text-3xs font-semibold text-gray-500 hover:text-gray-700">Cancelar</button>
                                            <button type="button" @click="$wire.editMessage({{ $msg->id }}, editText); editing = false;" class="text-3xs font-semibold text-indigo-600 hover:text-indigo-700">Guardar</button>
                                        </div>
                                    </div>
                                @endif

                                <!-- Hora -->
                                <div class="flex items-center justify-end gap-1.5 mt-1 text-[10px] text-gray-400">
                                    <span>{{ $msg->created_at->format('h:i a') }}</span>
                                    @if($isMe)
                                        <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <!-- Botón responder (hover) -->
                            <div class="absolute top-1/2 -translate-y-1/2 {{ $isMe ? '-left-9' : '-right-9' }} opacity-0 group-hover:opacity-100 transition-all duration-200">
                                <button wire:click="selectReplyMessage({{ $msg->id }})" title="Responder a este mensaje"
                                    class="p-1.5 rounded-full bg-white dark:bg-gray-700 shadow-md hover:shadow-lg text-gray-500 hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Avatar propio (solo mis mensajes) -->
                        @if($isMe)
                            <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white text-3xs font-bold ml-2 mt-auto shrink-0 shadow-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="w-20 h-20 rounded-full bg-white/80 dark:bg-gray-800/80 mx-auto mb-4 flex items-center justify-center shadow-lg">
                            <svg class="w-10 h-10 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-600">Aún no hay mensajes</p>
                        <p class="text-xs text-gray-400 mt-1">Envía el primer mensaje para iniciar la conversación del proyecto</p>
                    </div>
                @endforelse
            </div>

            <!-- Caja de Mensaje con Autocompletado @ (estilo WhatsApp) -->
            <div class="px-3 py-3 bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shrink-0 relative"
                x-data="{
                    newMessage: @entangle('newMessageText'),
                    mentionedIds: @entangle('mentionedUserIds'),
                    users: {{ json_encode($usersList) }},
                    filteredUsers: [],
                    showDropdown: false,
                    triggerChar: '@',
                    searchQuery: '',
                    
                    checkTrigger(e) {
                        const text = e.target.value || '';
                        const selectionEnd = e.target.selectionEnd;
                        const beforeCursor = text.slice(0, selectionEnd);
                        const lastAt = beforeCursor.lastIndexOf(this.triggerChar);
                        
                        if (lastAt !== -1 && (lastAt === 0 || beforeCursor[lastAt - 1] === ' ')) {
                            this.searchQuery = beforeCursor.slice(lastAt + 1);
                            if (!this.searchQuery.includes(' ')) {
                                this.filteredUsers = this.users.filter(u => u.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
                                this.showDropdown = this.filteredUsers.length > 0;
                                return;
                            }
                        }
                        this.showDropdown = false;
                    },
                    
                    insertMention(user) {
                        const text = this.newMessage || '';
                        const textarea = this.$refs.chatTextarea;
                        const selectionEnd = textarea.selectionEnd;
                        const beforeCursor = text.slice(0, selectionEnd);
                        const lastAt = beforeCursor.lastIndexOf(this.triggerChar);

                        const afterCursor = text.slice(selectionEnd);
                        const newText = beforeCursor.slice(0, lastAt) + '@' + user.name + ' ' + afterCursor;

                        this.newMessage = newText;
                        this.showDropdown = false;
                        if (!this.mentionedIds.includes(user.id)) {
                            this.mentionedIds.push(user.id);
                        }

                        this.$nextTick(() => {
                            textarea.focus();
                            const newCursorPos = lastAt + user.name.length + 2;
                            textarea.setSelectionRange(newCursorPos, newCursorPos);
                        });
                    },

                    handleKeydown(e) {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            if (this.newMessage && this.newMessage.trim().length > 0) {
                                $wire.sendMessage();
                            }
                        }
                    }
                }">
                
                <!-- Banner de Respuesta Activa -->
                @if($replyingToMessageId)
                    <div class="flex items-center justify-between p-2.5 mb-2 rounded-lg bg-white dark:bg-gray-800 border-l-4 border-indigo-500 shadow-sm text-xs">
                        <div class="truncate text-gray-700 dark:text-gray-300 flex-1">
                            <span class="font-bold text-indigo-600 dark:text-indigo-400 block text-2xs mb-0.5">Respondiendo a:</span> 
                            <span class="italic text-gray-500">"{{ Str::limit($replyingToMessageText, 60) }}"</span>
                        </div>
                        <button wire:click="clearReply" class="text-gray-400 hover:text-red-500 ml-3 p-1 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                <!-- Autocompletar Menciones Modal -->
                <div x-show="showDropdown" x-cloak
                    class="absolute left-3 right-3 bottom-full mb-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-44 overflow-y-auto z-50" x-transition>
                    <div class="p-1.5">
                        <template x-for="u in filteredUsers" :key="u.id">
                            <button type="button" @click="insertMention(u)"
                                class="w-full text-left px-3 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900 flex items-center gap-2.5 rounded-lg transition-colors">
                                <span class="w-7 h-7 rounded-full bg-indigo-500 flex items-center justify-center text-white text-3xs font-bold shrink-0" x-text="u.name.substring(0,2).toUpperCase()"></span>
                                <span class="font-semibold" x-text="u.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                @if($isParticipant)
                    <!-- Archivos seleccionados, pendientes de enviar -->
                    @if(!empty($attachments))
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            @foreach($attachments as $index => $attachment)
                                <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-full text-2xs text-gray-700 dark:text-gray-200">
                                    <x-heroicon-o-paper-clip class="w-3 h-3 text-gray-400" />
                                    {{ $attachment->getClientOriginalName() }}
                                    <button type="button" wire:click="removeAttachment({{ $index }})" class="p-0.5 text-gray-400 hover:text-red-500 rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </span>
                            @endforeach
                        </div>
                        @error('attachments.*') <span class="text-2xs text-red-500 block mb-1.5">{{ $message }}</span> @enderror
                    @endif

                    <div class="flex items-end gap-3 relative">
                        <label class="flex items-center justify-center w-10 h-10 shrink-0 mb-0.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 bg-white dark:bg-gray-700 rounded-full shadow-sm border border-gray-200 dark:border-gray-600 cursor-pointer transition-colors"
                            title="Adjuntar fotografía o archivo">
                            <input type="file" wire:model="attachments" multiple class="hidden" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx">
                            <x-heroicon-o-paper-clip class="w-5 h-5" />
                        </label>
                        <div class="flex-1 bg-white dark:bg-gray-700 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-600 overflow-hidden">
                            <textarea x-ref="chatTextarea" wire:model="newMessageText"
                                @keyup="checkTrigger" @input="checkTrigger"
                                @keydown="handleKeydown($event)"
                                rows="1"
                                placeholder="Escribe un mensaje..."
                                class="block w-full border-0 bg-transparent text-gray-900 dark:text-white px-4 py-3 text-sm focus:ring-0 focus:outline-none resize-none"
                                style="max-height: 120px; overflow-y: auto;"
                                x-on:input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'"></textarea>
                        </div>

                        <button wire:click="sendMessage"
                            class="flex items-center justify-center w-12 h-12 text-white bg-indigo-600 hover:bg-indigo-700 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 shrink-0 active:scale-90 mb-0.5"
                            title="Enviar mensaje (Enter)">
                            <svg class="w-5 h-5 ml-0.5 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </div>
                @else
                    <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 text-xs font-semibold">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        No eres participante de este proyecto. Pide a alguien de la pestaña "Participantes" que te agregue para poder escribir.
                    </div>
                @endif
            </div>
        </div>
        @elseif($activeTab === 'materiales')
        <!-- Lista de Materiales -->
        <div class="lg:col-span-2">
            <livewire:tenant.projects.project-materials :project-id="$project->id" :key="'materials-'.$project->id" />
        </div>
        @elseif($activeTab === 'participantes')
        <!-- Participantes -->
        <div class="lg:col-span-2">
            <livewire:tenant.projects.project-participants :project-id="$project->id" :key="'participants-'.$project->id" />
        </div>
        @elseif($activeTab === 'archivos')
        <!-- Archivos -->
        <div class="lg:col-span-2">
            <livewire:tenant.projects.project-files :project-id="$project->id" :key="'files-'.$project->id" />
        </div>
        @endif

    </div>

    <!-- Modales adicionales (Orden, Pregunta, Respuesta, Avance, Cierre) -->

    <!-- 1. Modal Orden de Producción -->
    @if($showOrderModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-2xl w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Crear Orden de Pedido y Producción</h3>
                <button wire:click="$set('showOrderModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Fecha de Entrega acordada (Global) *</label>
                    <input wire:model="delivery_date" type="date"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    @error('delivery_date') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                </div>

                <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200">Ítems de la Orden</h4>
                        <button type="button" wire:click="addOrderItem" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-semibold flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Añadir Ítem
                        </button>
                    </div>

                    @foreach($orderItems as $index => $item)
                    <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded border border-gray-200 dark:border-gray-700 mb-3 relative group">
                        @if(count($orderItems) > 1)
                        <button type="button" wire:click="removeOrderItem({{ $index }})" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                        @endif
                        
                        <div class="grid grid-cols-2 gap-4 mb-3 pr-6">
                            <div>
                                <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Cantidad *</label>
                                <input wire:model="orderItems.{{ $index }}.qty" type="number" min="1"
                                    class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                @error('orderItems.'.$index.'.qty') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Precio Unitario ($) *</label>
                                <input wire:model="orderItems.{{ $index }}.price_unit" type="number" step="0.01" min="0"
                                    class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                @error('orderItems.'.$index.'.price_unit') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Observaciones / Especificaciones (Opcional)</label>
                            <textarea wire:model="orderItems.{{ $index }}.observations" rows="2" placeholder="Detalles de este ítem..."
                                class="block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none"></textarea>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showOrderModal', false)" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded">Cancelar</button>
                <button wire:click="saveProductionOrder" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded shadow">Generar Orden</button>
            </div>
        </div>
    </div>
    @endif

    <!-- 2. Modal Crear Pregunta para el Cliente -->
    @if($showQuestionModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Generar Pregunta al Asesor/Cliente</h3>
                <button wire:click="$set('showQuestionModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Escribe la Pregunta *</label>
                    <textarea wire:model="newQuestionText" rows="4" placeholder="Escribe aquí de forma clara la duda sobre materiales o potencia para que el comercial la consulte con el cliente..."
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none"></textarea>
                    @error('newQuestionText') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showQuestionModal', false)" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded">Cancelar</button>
                <button wire:click="createQuestion" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded shadow">Enviar Pregunta</button>
            </div>
        </div>
    </div>
    @endif

    <!-- 3. Modal Responder Pregunta -->
    @if($showAnswerModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Registrar Respuesta del Cliente</h3>
                <button wire:click="$set('showAnswerModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Escribe la Respuesta de Cliente *</label>
                    <textarea wire:model="answerText" rows="4" placeholder="Escribe aquí la respuesta obtenida del cliente..."
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none"></textarea>
                    @error('answerText') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showAnswerModal', false)" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded">Cancelar</button>
                <button wire:click="saveAnswer" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded shadow">Guardar Respuesta</button>
            </div>
        </div>
    </div>
    @endif

    <!-- 4. Modal Registrar Avance de Laboratorio -->
    @if($showAdvanceModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Registrar Avance Técnico</h3>
                <button wire:click="$set('showAdvanceModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Descripción del Avance *</label>
                    <textarea wire:model="advanceDescription" rows="3" placeholder="Ej: Material recibido del almacén, ensamblado inicial completado..."
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none"></textarea>
                    @error('advanceDescription') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Porcentaje de Avance General ({{ $advanceModalLastPercentage }} - 100%) *</label>
                    <input wire:model="advancePercentage" type="number" min="{{ $advanceModalLastPercentage }}" max="100"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    <p class="text-3xs text-gray-400 mt-0.5">Último avance registrado: {{ $advanceModalLastPercentage }}%. No puedes registrar un porcentaje menor.</p>
                    @error('advancePercentage') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showAdvanceModal', false)" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded">Cancelar</button>
                <button wire:click="addAdvance" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded shadow">Guardar Avance</button>
            </div>
        </div>
    </div>
    @endif

    <!-- 4b. Modal Registrar Novedad del Cliente -->
    @if($showNoveltyModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Registrar Novedad del Cliente</h3>
                <button wire:click="$set('showNoveltyModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Descripción de la Novedad *</label>
                    <textarea wire:model="noveltyDescription" rows="3" placeholder="Ej: El cliente solicita una unidad más..."
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none"></textarea>
                    @error('noveltyDescription') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showNoveltyModal', false)" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded">Cancelar</button>
                <button wire:click="addNovelty" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded shadow">Guardar Novedad</button>
            </div>
        </div>
    </div>
    @endif

    <!-- 5. Modal Terminar Producción (Laboratorio) -->
    @if($showLabFinishModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase">{{ $project->type === 'internal' ? 'Marcar como Terminado' : 'Cierre de Producción' }}</h3>
                <button wire:click="$set('showLabFinishModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Fecha de Terminación *</label>
                    <input wire:model="completion_date" type="date"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    @error('completion_date') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Observaciones Finales / Calidad</label>
                    <textarea wire:model="lab_observations" rows="3" placeholder="Ingresa detalles sobre pruebas de calidad o empaque del producto terminado..."
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showLabFinishModal', false)" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded">Cancelar</button>
                <button wire:click="finishProduction" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded shadow">{{ $project->type === 'internal' ? 'Marcar como Terminado' : 'Terminar Producción' }}</button>
            </div>
        </div>
    </div>
    @endif

    <!-- 6. Modal Cerrar y Archivar Proyecto (Comercial) -->
    @if($showCloseModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase">{{ $project->type === 'internal' ? 'Finalizar Proyecto' : 'Registrar Entrega' }}</h3>
                <button wire:click="$set('showCloseModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Fecha Real de Entrega *</label>
                    <input wire:model="real_delivery_date" type="date"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    @error('real_delivery_date') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Comentarios de Cierre / Firma del Cliente</label>
                    <textarea wire:model="close_observations" rows="3" placeholder="Detalles de entrega, inconformidades resueltas o conformidad del cliente..."
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showCloseModal', false)" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded">Cancelar</button>
                <button wire:click="closeProject" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded shadow">{{ $project->type === 'internal' ? 'Finalizar Proyecto' : 'Registrar Entrega' }}</button>
            </div>
        </div>
    </div>
    @endif

    <!-- 7. Modal Iniciar Desarrollo (Proyecto Interno) -->
    @if($showStartDevelopmentModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Iniciar Desarrollo</h3>
                <button wire:click="$set('showStartDevelopmentModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <span class="text-2xs text-gray-500 dark:text-gray-400 block mb-2">Fecha de entrega solicitada: <strong>{{ $project->delivery_date ? $project->delivery_date->format('d/m/Y') : 'No establecida' }}</strong></span>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Fecha Sugerida de Entrega *</label>
                    <input wire:model="suggested_delivery_date" type="date"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    @error('suggested_delivery_date') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showStartDevelopmentModal', false)" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded">Cancelar</button>
                <button wire:click="startInternalDevelopment" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded shadow">Iniciar Desarrollo</button>
            </div>
        </div>
    </div>
    @endif

</div>
