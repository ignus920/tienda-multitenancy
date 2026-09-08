@if($showDetailModal && $detailTask)
<div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-start justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    @include('livewire.tenant.task-planner.partials.priority-badge', ['task' => $detailTask])
                    @include('livewire.tenant.task-planner.partials.status-badge', ['task' => $detailTask])
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $detailTask->title }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $detailTask->department->name ?? '—' }}</p>
            </div>
            <button wire:click="$set('showDetailModal', false)" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="p-6 space-y-5">
            @if($detailTask->description)
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $detailTask->description }}</p>
            @endif

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div><span class="text-gray-400">Responsables:</span> {{ $detailTask->assignments->map(fn($a) => $a->user->name ?? '—')->join(', ') }}</div>
                <div><span class="text-gray-400">Duración estimada:</span> {{ intdiv($detailTask->estimated_minutes, 60) }}h {{ $detailTask->estimated_minutes % 60 }}min</div>
                <div><span class="text-gray-400">Fecha límite:</span> {{ $detailTask->deadline_at?->format('d/m/Y H:i') ?? '—' }}</div>
                <div><span class="text-gray-400">Ubicación:</span> {{ ucfirst($detailTask->location_type) }} {{ $detailTask->location }}</div>
            </div>

            <!-- SECCIONES INTEGRADAS: Materiales, Checklists y Adjuntos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                @if($detailTask->materials->isNotEmpty())
                <div class="bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                    <h4 class="text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Materiales / Herramientas</h4>
                    <ul class="text-xs space-y-1">
                        @foreach($detailTask->materials as $mat)
                        <li class="flex items-center justify-between text-gray-700 dark:text-gray-300">
                            <span>
                                @if($mat->item_id)
                                    <span class="text-blue-500 mr-1" title="Del Inventario">📦</span>{{ $mat->item->name ?? 'Ítem eliminado' }}
                                @else
                                    <span class="text-gray-400 mr-1" title="Genérico">🛠️</span>{{ $mat->name }}
                                @endif
                            </span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ (float) $mat->estimated_quantity }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if($detailTask->checklists->isNotEmpty())
                <div class="bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                    <h4 class="text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Checklist de Tarea</h4>
                    <ul class="text-xs space-y-1">
                        @foreach($detailTask->checklists as $chk)
                        <li class="flex items-start gap-2">
                            @if($chk->is_completed)
                            <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            <span class="text-gray-500 line-through">{{ $chk->description }}@if($chk->is_required)<span class="text-amber-500 font-bold">*</span>@endif</span>
                            @else
                            <div class="w-4 h-4 rounded-full border-2 {{ $chk->is_required ? 'border-amber-400' : 'border-gray-300 dark:border-gray-500' }} shrink-0"></div>
                            <span class="text-gray-700 dark:text-gray-300">{{ $chk->description }}@if($chk->is_required)<span class="text-amber-500 font-bold" title="Obligatorio">*</span>@endif</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

            </div>

            @if($detailTask->attachments->isNotEmpty())
            <div>
                <h4 class="text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Archivos Adjuntos</h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($detailTask->attachments as $att)
                    <a href="{{ Storage::url($att->file_path) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        {{ $att->file_name }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif


            @if($detailTask->status === 'bloqueada')
            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-3 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-purple-700 dark:text-purple-300">Bloqueada</p>
                    <p class="text-xs text-purple-600 dark:text-purple-400">{{ $detailTask->blocked_reason }}</p>
                </div>
                <button wire:click="unblockTask({{ $detailTask->id }})" class="text-xs font-semibold text-purple-700 hover:underline">Desbloquear</button>
            </div>
            @else
            <button wire:click="openBlockModal({{ $detailTask->id }})" class="text-xs font-semibold text-purple-600 hover:underline">Marcar como bloqueada</button>
            @endif



            @if($detailTask->pauses->isNotEmpty())
            <div>
                <h4 class="text-xs font-bold text-gray-600 dark:text-gray-300 mb-1">Pausas</h4>
                <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                    @foreach($detailTask->pauses as $pause)
                    <li>{{ $pause->user->name ?? '—' }} · {{ \App\Models\Tenant\TaskPlanner\TaskPause::REASONS[$pause->reason] ?? $pause->reason }} ·
                        {{ $pause->started_at->format('d/m H:i') }}{{ $pause->ended_at ? ' - '.$pause->ended_at->format('H:i') : ' (en curso)' }}
                        @if($pause->observation) — {{ $pause->observation }} @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div>
                <h4 class="text-xs font-bold text-gray-600 dark:text-gray-300 mb-2">Observaciones</h4>
                <div class="space-y-2 max-h-40 overflow-y-auto mb-2">
                    @forelse($detailTask->comments as $comment)
                    <div class="text-xs bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                        <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $comment->user->name ?? '—' }}</span>
                        <span class="text-gray-400"> · {{ $comment->created_at->format('d/m H:i') }}</span>
                        <p class="text-gray-600 dark:text-gray-300">{{ $comment->comment }}</p>
                    </div>
                    @empty
                    <p class="text-xs text-gray-400">Sin observaciones aún.</p>
                    @endforelse
                </div>
                <div class="flex gap-2">
                    <input wire:model="newComment" wire:keydown.enter="addDetailComment" type="text" placeholder="Agregar observación..."
                        class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs">
                    <button wire:click="addDetailComment" class="px-3 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shrink-0">Enviar</button>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-gray-600 dark:text-gray-300 mb-2">Historial</h4>
                <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-1 max-h-32 overflow-y-auto">
                    @foreach($detailTask->history as $h)
                    <li>
                        {{ $h->created_at->format('d/m H:i') }} — {{ $h->user->name ?? 'Sistema' }}: {{ $h->action }}
                        @if($h->new_value) → {{ $h->new_value }} @endif
                        @if($h->reason) ({{ $h->reason }}) @endif
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endif
