<div class="relative" x-data="{
        open: false,
        playSound() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
                oscillator.frequency.setValueAtTime(1100, audioCtx.currentTime + 0.1);
                gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
                oscillator.start(audioCtx.currentTime);
                oscillator.stop(audioCtx.currentTime + 0.5);
            } catch(e) { console.warn('No se pudo reproducir sonido:', e); }
        }
    }"
    x-on:click.away="open = false"
    @play-notification-sound.window="playSound()"
    wire:poll.60s="loadNotifications"
>
    {{-- Botón de Campanita --}}
    <button type="button"
            class="relative p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            @click="open = !open"
            id="notification-bell-button">
        {{-- Ícono de Campana --}}
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>

        {{-- Badge rojo (Punto indicador global con animación radar) --}}
        @if(($unreadCount + $pendingCount + $taskCount) > 0)
            <span class="absolute top-1 right-1 flex items-center justify-center">
                <span class="animate-ping absolute inline-flex h-5 w-5 rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500 ring-2 ring-white dark:ring-gray-800"></span>
            </span>
        @endif
    </button>

    {{-- Dropdown de Notificaciones --}}
    <div x-show="open"
         x-data="{ tab: 'general' }"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-96 max-h-[32rem] origin-top-right rounded-xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-black/5 dark:ring-gray-700 overflow-hidden z-50 flex flex-col"
         style="display: none;">

        {{-- Título Principal --}}
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Notificaciones</h3>
        </div>

        {{-- Encabezado con Pestañas --}}
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 shrink-0 bg-gray-50/50 dark:bg-gray-800/50">
            <div class="flex w-full">
                <button @click="tab = 'general'" 
                        :class="{ 'text-indigo-600 dark:text-indigo-400': tab === 'general', 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300': tab !== 'general' }"
                        class="relative flex-1 py-3 text-xs font-semibold text-center focus:outline-none transition-colors">
                    Notif. del Proyecto
                    @if($unreadCount > 0)
                        <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400">
                            {{ $unreadCount }}
                        </span>
                    @endif
                    <span x-show="tab === 'general'" class="absolute bottom-0 inset-x-0 h-0.5 bg-indigo-600 dark:bg-indigo-400 rounded-t-full"></span>
                </button>
                <button @click="tab = 'pendientes'" 
                        :class="{ 'text-indigo-600 dark:text-indigo-400': tab === 'pendientes', 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300': tab !== 'pendientes' }"
                        class="relative flex-1 py-3 text-xs font-semibold text-center focus:outline-none transition-colors">
                    Pendientes
                    @if($pendingCount > 0)
                        <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400">
                            {{ $pendingCount }}
                        </span>
                    @endif
                    <span x-show="tab === 'pendientes'" class="absolute bottom-0 inset-x-0 h-0.5 bg-indigo-600 dark:bg-indigo-400 rounded-t-full"></span>
                </button>
                <button @click="tab = 'tareas'" 
                        :class="{ 'text-indigo-600 dark:text-indigo-400': tab === 'tareas', 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300': tab !== 'tareas' }"
                        class="relative flex-1 py-3 text-xs font-semibold text-center focus:outline-none transition-colors">
                    Tareas
                    @if($taskCount > 0)
                        <span class="ml-1 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-400">
                            {{ $taskCount }}
                        </span>
                    @endif
                    <span x-show="tab === 'tareas'" class="absolute bottom-0 inset-x-0 h-0.5 bg-indigo-600 dark:bg-indigo-400 rounded-t-full"></span>
                </button>
            </div>
        </div>

        {{-- Tab: Notificaciones Generales --}}
        <div x-show="tab === 'general'" class="overflow-y-auto max-h-[25rem] divide-y divide-gray-50 dark:divide-gray-700/50">
            @forelse($notifications as $notification)
                <button wire:click="markAsRead({{ $notification['id'] }})"
                        class="w-full flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-left">
                    {{-- Avatar del remitente --}}
                    <div class="flex-shrink-0">
                        @if($notification['sender_avatar'])
                            <img src="{{ $notification['sender_avatar'] }}" alt="{{ $notification['sender_name'] }}"
                                 class="h-9 w-9 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-600">
                        @else
                            <div class="h-9 w-9 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                                <span class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ strtoupper(substr($notification['sender_name'], 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                {{ $notification['sender_name'] }}
                            </span>
                            @if(in_array($notification['type'], ['mencion', 'mencion_avance', 'respuesta_avance']))
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300">
                                    {{ in_array($notification['type'], ['mencion_avance', 'respuesta_avance']) ? 'Avances de Proyectos' : '@mención' }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                            {{ $notification['project_title'] }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 line-clamp-2">
                            {{ mb_substr($notification['message_preview'], 0, 60) }}{{ mb_strlen($notification['message_preview']) > 60 ? '...' : '' }}
                        </p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1">
                            {{ $notification['time_ago'] }}
                        </p>
                    </div>

                    {{-- Punto indicador de no leída --}}
                    <div class="flex-shrink-0 mt-1">
                        <span class="block h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                    </div>
                </button>
            @empty
                <div class="px-4 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 font-medium">Bandeja al día</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">No tienes notificaciones pendientes.</p>
                </div>
            @endforelse
        </div>

        {{-- Tab: Pendientes (Parlante) --}}
        <div x-show="tab === 'pendientes'" style="display: none;" class="overflow-y-auto max-h-[25rem] divide-y divide-gray-50 dark:divide-gray-700/50 bg-orange-50/30 dark:bg-orange-900/10">
            @forelse($pendingMentions as $question)
                <a href="{{ route('tenant.projects.workspace', $question['project_id']) }}"
                   class="w-full flex items-start gap-3 px-4 py-3 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors text-left group">
                    
                    {{-- Avatar del destinatario --}}
                    <div class="flex-shrink-0 mt-1">
                        @if(isset($question['recipient_avatar']) && $question['recipient_avatar'])
                            <img src="{{ $question['recipient_avatar'] }}" alt="{{ $question['recipient_name'] }}"
                                 class="h-9 w-9 rounded-full object-cover ring-2 ring-orange-200 dark:ring-orange-800">
                        @else
                            <div class="h-9 w-9 rounded-full bg-orange-100 dark:bg-orange-900/50 flex items-center justify-center ring-2 ring-orange-200 dark:ring-orange-800">
                                <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">
                                    {{ strtoupper(substr($question['recipient_name'] ?? 'U', 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                Esperando a: {{ $question['recipient_name'] }}
                            </span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300">
                                Pendiente
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                            {{ $question['project_title'] }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 line-clamp-2 italic">
                            "{{ mb_substr($question['question_preview'], 0, 60) }}{{ mb_strlen($question['question_preview']) > 60 ? '...' : '' }}"
                        </p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5">
                            Hace {{ $question['time_ago'] }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="px-4 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-orange-200 dark:text-orange-900/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 font-medium">Nadie te debe respuestas</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Todos tus pendientes han sido atendidos.</p>
                </div>
            @endforelse
        </div>

        {{-- Tab: Tareas --}}
        <div x-show="tab === 'tareas'" style="display: none;" class="overflow-y-auto max-h-[25rem] divide-y divide-gray-50 dark:divide-gray-700/50 bg-blue-50/30 dark:bg-blue-900/10">
            @forelse($pendingTasks as $task)
                <a href="{{ route('tenant.projects.workspace', $task['project_id']) }}"
                   class="w-full flex items-start gap-3 px-4 py-3 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors text-left group">
                    
                    {{-- Avatar del asignador --}}
                    <div class="flex-shrink-0 mt-1">
                        @if(isset($task['creator_avatar']) && $task['creator_avatar'])
                            <img src="{{ $task['creator_avatar'] }}" alt="{{ $task['creator_name'] }}"
                                 class="h-9 w-9 rounded-full object-cover ring-2 ring-blue-200 dark:ring-blue-800">
                        @else
                            <div class="h-9 w-9 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center ring-2 ring-blue-200 dark:ring-blue-800">
                                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    {{ strtoupper(substr($task['creator_name'] ?? 'U', 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                Asignó: {{ $task['creator_name'] }}
                            </span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                Tarea Pendiente
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                            {{ $task['project_title'] }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 line-clamp-2 font-bold">
                            {{ $task['title'] }}
                        </p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5">
                            Hace {{ $task['time_ago'] }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="px-4 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-blue-200 dark:text-blue-900/50" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 font-medium">Libre de Tareas</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">No tienes tareas asignadas pendientes.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
