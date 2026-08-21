<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ 
    scrollToBottom() {
        const container = this.$refs.chatContainer;
        if(container) {
            container.scrollTop = container.scrollHeight;
        }
    }
}" x-init="$nextTick(() => scrollToBottom())" @message-sent.window="$nextTick(() => scrollToBottom())">

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
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $project->title }}</h1>
            <p class="text-xs text-indigo-600 dark:text-indigo-400 font-bold mt-0.5">
                Cliente: {{ $project->customer->businessName ?? trim(($project->customer->firstName ?? '') . ' ' . ($project->customer->lastName ?? '')) }}
            </p>
        </div>
        <!-- Selector de Estado Visual (Pipeline) -->
        <div class="flex items-center gap-2">
            @php
                $statuses = ['cotizacion' => 'Cotización', 'negociacion' => 'Negociación', 'orden_creada' => 'Orden Creada', 'en_produccion' => 'En Producción', 'terminado' => 'Terminado', 'archivados' => 'Archivado'];
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
                    @if($project->qty)
                        <div class="bg-gray-50 dark:bg-gray-850 p-3 rounded-lg border border-gray-100 dark:border-gray-750 grid grid-cols-2 gap-3">
                            <div class="col-span-2 text-2xs font-bold text-indigo-600 dark:text-indigo-400 uppercase border-b border-indigo-100 dark:border-indigo-900 pb-1">
                                Orden de Pedido / Producción
                            </div>
                            <div>
                                <span class="text-gray-400 block">Cantidad:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $project->qty }} unidades</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block">Precio Unit:</span>
                                <span class="font-bold text-gray-900 dark:text-white">${{ number_format($project->price_unit, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block">Valor Total:</span>
                                <span class="font-bold text-gray-900 dark:text-white">${{ number_format($project->total_value, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block">Fecha Entrega:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $project->delivery_date ? $project->delivery_date->format('d/m/Y') : 'No establecida' }}</span>
                            </div>
                            @if($project->prod_observations)
                                <div class="col-span-2">
                                    <span class="text-gray-400 block">Observaciones Producción:</span>
                                    <p class="text-gray-700 dark:text-gray-300 mt-0.5">{{ $project->prod_observations }}</p>
                                </div>
                            @endif
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
                        <!-- Comercial genera Orden -->
                        @if(in_array($project->status, ['cotizacion', 'negociacion']))
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

                        <!-- Laboratorio agrega avances y preguntas en producción -->
                        @if($project->status === 'en_produccion')
                            <button wire:click="$set('showAdvanceModal', true)"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 text-gray-800 dark:text-white rounded-lg font-bold shadow-2xs transition-colors">
                                Registrar Avance Técnico
                            </button>

                            <button wire:click="$set('showQuestionModal', true)"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-bold shadow-sm transition-colors">
                                Generar Pregunta al Asesor/Cliente
                            </button>

                            <button wire:click="$set('showLabFinishModal', true)"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold shadow-sm transition-colors mt-2">
                                Terminar Producción
                            </button>
                        @endif

                        <!-- Comercial cierra y archiva el caso -->
                        @if($project->status === 'terminado')
                            <div class="bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300 p-2.5 rounded-lg text-center font-semibold mb-2">
                                ¡Producción Terminada! Listo para entregar al cliente.
                            </div>
                            <button wire:click="$set('showCloseModal', true)"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold shadow-sm transition-colors">
                                Registrar Entrega y Archivar
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

        </div>

        <!-- Columna Derecha: Chat Interactivo estilo WhatsApp -->
        <div class="lg:col-span-2 flex flex-col h-[70vh] bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Header del Chat -->
            <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-850 border-b border-gray-100 dark:border-gray-750 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Foro del Proyecto</span>
                </div>
                
                <!-- Filtros del chat -->
                <div class="flex items-center gap-2 self-stretch sm:self-auto">
                    <select wire:model.live="chatFilterRole" 
                        class="py-1 px-2 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-750 text-gray-900 dark:text-white rounded text-2xs focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <option value="">Todos los roles</option>
                        <option value="2">Comercial</option>
                        <option value="8">Laboratorio</option>
                        <option value="1">Gerencia / Admin</option>
                    </select>
                </div>
            </div>

            <!-- Contenedor del Chat (Mensajes) -->
            <div x-ref="chatContainer" class="flex-1 overflow-y-auto p-5 space-y-4 bg-gray-50/50 dark:bg-gray-900/10">
                @forelse($messages as $msg)
                    @php
                        $isMe = $msg->user_id === Auth::id();
                        // Asignación de colores por rol
                        $roleColors = [
                            2 => 'text-indigo-600 dark:text-indigo-400',
                            8 => 'text-pink-600 dark:text-pink-400',
                            1 => 'text-emerald-600 dark:text-emerald-400',
                        ];
                        $userRoleColor = $roleColors[$msg->user->profile_id] ?? 'text-gray-500';
                    @endphp

                    <!-- Burbuja de mensaje -->
                    <div id="msg-{{ $msg->id }}" class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} group relative">
                        <div class="max-w-[85%] rounded-2xl px-4 py-2.5 shadow-2xs {{ $isMe ? 'bg-indigo-650 text-white rounded-tr-xs' : 'bg-white dark:bg-gray-850 text-gray-900 dark:text-white border border-gray-100 dark:border-gray-750 rounded-tl-xs' }}">
                            
                            <!-- Remitente (solo en mensajes de otros) -->
                            @if(!$isMe)
                                <div class="text-3xs font-extrabold uppercase {{ $userRoleColor }} mb-1">
                                    {{ $msg->user->name }}
                                </div>
                            @endif

                            <!-- Cita / Respuesta a un mensaje anterior -->
                            @if($msg->repliedTo)
                                <div @click="
                                    const el = document.getElementById('msg-{{ $msg->reply_to_id }}');
                                    if(el) {
                                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                        el.classList.add('ring-2', 'ring-indigo-500', 'transition-all');
                                        setTimeout(() => el.classList.remove('ring-2', 'ring-indigo-500'), 2000);
                                    }
                                " class="mb-2 p-2 rounded bg-black/5 dark:bg-white/5 border-l-2 border-indigo-400 text-3xs cursor-pointer flex flex-col gap-0.5 select-none hover:bg-black/10 dark:hover:bg-white/10 transition-colors">
                                    <span class="font-bold text-gray-500 dark:text-gray-400">Respuesta a {{ $msg->repliedTo->user->name }}:</span>
                                    <p class="truncate italic text-gray-600 dark:text-gray-300">"{{ $msg->repliedTo->message }}"</p>
                                </div>
                            @endif

                            <!-- Contenido del mensaje -->
                            <p class="text-xs leading-relaxed break-words font-medium">
                                {!! preg_replace('/(@[a-zA-Z0-9_\-\.]+)/', '<span class="font-bold text-amber-500">$1</span>', e($msg->message)) !!}
                            </p>

                            <!-- Hora y acciones -->
                            <div class="flex items-center justify-end gap-2 mt-1.5 text-3xs {{ $isMe ? 'text-indigo-200' : 'text-gray-400' }}">
                                <span>{{ $msg->created_at->format('H:i') }}</span>
                            </div>
                        </div>

                        <!-- Botón responder (Aparece en hover) -->
                        <div class="absolute top-1/2 -translate-y-1/2 {{ $isMe ? 'left-0 -ml-8' : 'right-0 -mr-8' }} opacity-0 group-hover:opacity-100 transition-opacity">
                            <button wire:click="selectReplyMessage({{ $msg->id }})" title="Responder a este mensaje"
                                class="p-1 rounded-full bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-400 dark:text-gray-500">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="text-xs">No hay mensajes. Comienza la conversación aquí.</p>
                    </div>
                @endforelse
            </div>

            <!-- Caja de Mensaje con Autocompletado @ -->
            <div class="p-4 border-t border-gray-100 dark:border-gray-750 bg-white dark:bg-gray-800 flex flex-col gap-2 relative"
                x-data="{
                    newMessage: @entangle('newMessageText'),
                    users: {{ json_encode($usersList) }},
                    filteredUsers: [],
                    showDropdown: false,
                    triggerChar: '@',
                    searchQuery: '',
                    
                    checkTrigger(e) {
                        const text = this.newMessage || '';
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
                    
                    insertMention(name) {
                        const text = this.newMessage || '';
                        const textarea = this.$refs.chatTextarea;
                        const selectionEnd = textarea.selectionEnd;
                        const beforeCursor = text.slice(0, selectionEnd);
                        const lastAt = beforeCursor.lastIndexOf(this.triggerChar);
                        
                        const afterCursor = text.slice(selectionEnd);
                        const newText = beforeCursor.slice(0, lastAt) + '@' + name + ' ' + afterCursor;
                        
                        this.newMessage = newText;
                        this.showDropdown = false;
                        
                        this.$nextTick(() => {
                            textarea.focus();
                            const newCursorPos = lastAt + name.length + 2;
                            textarea.setSelectionRange(newCursorPos, newCursorPos);
                        });
                    }
                }">
                
                <!-- Banner de Respuesta Activa -->
                @if($replyingToMessageId)
                    <div class="flex items-center justify-between p-2 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-150 dark:border-indigo-900 text-xs">
                        <div class="truncate text-indigo-700 dark:text-indigo-400">
                            <span class="font-bold">Respondiendo a:</span> {{ $replyingToMessageText }}
                        </div>
                        <button wire:click="clearReply" class="text-indigo-500 hover:text-indigo-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                <!-- Autocompletar Menciones Modal -->
                <div x-show="showDropdown" x-cloak
                    class="absolute left-4 right-4 bottom-full mb-1 bg-white dark:bg-gray-850 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-36 overflow-y-auto z-50">
                    <template x-for="u in filteredUsers" :key="u.id">
                        <button type="button" @click="insertMention(u.name)"
                            class="w-full text-left px-4 py-2 text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750 flex items-center gap-2 border-b border-gray-50 dark:border-gray-750">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <span class="font-bold" x-text="u.name"></span>
                        </button>
                    </template>
                </div>

                <div class="flex gap-2">
                    <textarea x-ref="chatTextarea" wire:model="newMessageText" @keyup="checkTrigger" @input="checkTrigger" rows="2"
                        placeholder="Escribe un mensaje aquí... Usa @ para mencionar a alguien"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none resize-none"></textarea>
                    
                    <button wire:click="sendMessage"
                        class="inline-flex items-center justify-center p-3 text-white bg-indigo-650 hover:bg-indigo-750 rounded-lg shadow transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Modales adicionales (Orden, Pregunta, Respuesta, Avance, Cierre) -->

    <!-- 1. Modal Orden de Producción -->
    @if($showOrderModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Crear Orden de Pedido y Producción</h3>
                <button wire:click="$set('showOrderModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Cantidad *</label>
                        <input wire:model="qty" type="number" min="1"
                            class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                        @error('qty') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Precio Unitario ($) *</label>
                        <input wire:model="price_unit" type="number" step="0.01" min="0"
                            class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                        @error('price_unit') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Fecha de Entrega acordada *</label>
                    <input wire:model="delivery_date" type="date"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    @error('delivery_date') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Observaciones / Especificaciones técnicas</label>
                    <textarea wire:model="prod_observations" rows="3" placeholder="Tipo de led, drivers necesarios, colores de cables..."
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showOrderModal', false)" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded">Cancelar</button>
                <button wire:click="saveProductionOrder" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-650 hover:bg-indigo-750 rounded shadow">Generar Orden</button>
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
                <button wire:click="createQuestion" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-650 hover:bg-indigo-750 rounded shadow">Enviar Pregunta</button>
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
                <button wire:click="saveAnswer" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-650 hover:bg-indigo-750 rounded shadow">Guardar Respuesta</button>
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
                    <label class="block text-3xs font-bold text-gray-700 dark:text-gray-300 uppercase mb-1">Porcentaje de Avance General (0 - 100%) *</label>
                    <input wire:model="advancePercentage" type="number" min="0" max="100"
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    @error('advancePercentage') <span class="text-2xs text-red-500 block mt-0.5 font-semibold">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
                <button wire:click="$set('showAdvanceModal', false)" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 rounded">Cancelar</button>
                <button wire:click="addAdvance" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-650 hover:bg-indigo-750 rounded shadow">Guardar Avance</button>
            </div>
        </div>
    </div>
    @endif

    <!-- 5. Modal Terminar Producción (Laboratorio) -->
    @if($showLabFinishModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Cierre de Producción</h3>
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
                <button wire:click="finishProduction" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-650 hover:bg-indigo-750 rounded shadow">Terminar Producción</button>
            </div>
        </div>
    </div>
    @endif

    <!-- 6. Modal Cerrar y Archivar Proyecto (Comercial) -->
    @if($showCloseModal)
    <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase">Registrar Entrega y Archivar</h3>
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
                <button wire:click="closeProject" type="button" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-indigo-650 hover:bg-indigo-750 rounded shadow">Guardar y Archivar</button>
            </div>
        </div>
    </div>
    @endif

</div>
