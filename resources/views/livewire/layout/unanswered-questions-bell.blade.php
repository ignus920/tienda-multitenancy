<div class="relative ml-2" x-data="{ open: false }" x-on:click.away="open = false" wire:poll.60s="loadQuestions">
    {{-- Botón de Parlante --}}
    <button type="button"
            class="relative p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            @click="open = !open"
            title="Mis preguntas sin responder"
            id="unanswered-questions-bell-button">
        
        {{-- Ícono de Parlante (Megáfono) --}}
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z" />
        </svg>

        {{-- Badge rojo con contador --}}
        @if($unansweredCount > 0)
            <span class="absolute top-0 right-0 flex items-center justify-center h-4 min-w-[1rem] px-1 text-[9px] font-bold text-white bg-red-500 rounded-full ring-2 ring-white dark:ring-gray-800 animate-pulse">
                {{ $unansweredCount > 99 ? '99+' : $unansweredCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown de Preguntas --}}
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
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-red-50 to-orange-50 dark:from-gray-800 dark:to-gray-900">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Tus Preguntas sin Responder
            </h3>
        </div>

        {{-- Lista de Preguntas --}}
        <div class="overflow-y-auto max-h-[22rem] divide-y divide-gray-50 dark:divide-gray-700/50">
            @forelse($questions as $question)
                <a href="{{ route('tenant.projects.workspace', $question['project_id']) }}"
                   class="w-full flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-left group">
                    
                    {{-- Icono representativo --}}
                    <div class="flex-shrink-0 mt-1">
                        <div class="h-8 w-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center group-hover:bg-red-200 dark:group-hover:bg-red-900/50 transition-colors">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Contenido --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                {{ $question['project_title'] }}
                            </span>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 flex-shrink-0">
                                {{ $question['time_ago'] }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 line-clamp-2">
                            "{{ $question['question_preview'] }}"
                        </p>
                        <p class="text-[10px] font-semibold text-red-500 mt-1.5 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                            Pendiente de respuesta
                        </p>
                    </div>
                </a>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Excelente, todas tus preguntas han sido respondidas.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
