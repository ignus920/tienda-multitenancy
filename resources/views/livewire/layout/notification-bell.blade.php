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

        {{-- Badge rojo con contador --}}
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 flex items-center justify-center h-4 min-w-[1rem] px-1 text-[9px] font-bold text-white bg-red-500 rounded-full ring-2 ring-white dark:ring-gray-800 animate-pulse">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown de Notificaciones --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-96 max-h-[28rem] origin-top-right rounded-xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-black/5 dark:ring-gray-700 overflow-hidden z-50"
         style="display: none;">

        {{-- Encabezado --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notificaciones Proyectos</h3>
        </div>

        {{-- Lista de Notificaciones --}}
        <div class="overflow-y-auto max-h-[22rem] divide-y divide-gray-50 dark:divide-gray-700/50">
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
                            @if(in_array($notification['type'], ['mencion', 'mencion_avance']))
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300">
                                    {{ $notification['type'] === 'mencion_avance' ? 'Avances de Proyectos' : '@mención' }}
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
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Sin notificaciones pendientes</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
