@if($showProposalModal && !empty($proposalGroups))
<div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Reorganización propuesta</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                La nueva tarea pisó la agenda. Esto es lo que el sistema propone mover
                (respeta prioridades y horario laboral). Nada se cambia hasta que aceptes.
            </p>
        </div>

        <div class="p-6 space-y-5">
            @foreach($proposalGroups as $group)
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">{{ $group['user_name'] }}</p>
                <div class="space-y-2">
                    @foreach($group['rows'] as $row)
                    <div class="rounded-lg border {{ $row['overflow'] ? 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20' : 'border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30' }} p-3 text-xs">
                        <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $row['title'] }} <span class="font-normal text-gray-400">· {{ $row['priority'] }}</span></p>
                        <p class="text-gray-500 dark:text-gray-400 mt-0.5">
                            <span class="line-through">{{ $row['old_label'] }}</span>
                            <span class="mx-1">→</span>
                            <span class="font-semibold {{ $row['overflow'] ? 'text-red-600' : ($row['changed'] ? 'text-indigo-600 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-300') }}">
                                {{ $row['new_label'] }}
                            </span>
                            @if(!$row['overflow'] && !$row['changed'])
                                <span class="text-gray-400">(sin cambio)</span>
                            @endif
                        </p>
                        @if(!empty($row['deadline_exceeded']))
                            <p class="text-red-500 font-semibold mt-1">⚠ Quedaría después de la fecha límite.</p>
                        @endif
                        @if(!empty($row['overflow']))
                            <p class="text-red-500 font-semibold mt-1">⚠ No cabe hoy: hay que reprogramarla manualmente a otro día.</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            <button wire:click="dismissProposal" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200">
                Ajustar manualmente
            </button>
            <button wire:click="acceptProposal" class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">
                Aceptar reprogramación
            </button>
        </div>
    </div>
</div>
@endif
