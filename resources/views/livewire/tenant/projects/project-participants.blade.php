<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-6">
    <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Participantes del Proyecto</h2>

    <!-- Agregar participante -->
    <div class="bg-gray-50 dark:bg-gray-850 rounded-lg p-4 border border-gray-100 dark:border-gray-750">
        <span class="text-2xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider block mb-2">Agregar participante</span>
        <div class="flex flex-col md:flex-row gap-2">
            <select wire:model="selectedUserId"
                class="flex-1 block w-full border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">Selecciona un usuario...</option>
                @foreach($availableUsers as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <button wire:click="addParticipant" type="button"
                class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow transition-colors whitespace-nowrap">
                Agregar
            </button>
        </div>
    </div>

    <!-- Lista de participantes actuales -->
    <div class="space-y-2">
        @forelse($participants as $participant)
            <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100 dark:border-gray-750 bg-white dark:bg-gray-850">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-white text-2xs font-bold shrink-0">
                        {{ strtoupper(substr($participant->user->name ?? '?', 0, 2)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $participant->user->name ?? 'Usuario eliminado' }}</p>
                        <p class="text-2xs text-gray-400">{{ $participant->role }}</p>
                    </div>
                </div>
                <button wire:click="removeParticipant({{ $participant->id }})" wire:confirm="¿Quitar a {{ $participant->user->name ?? 'este usuario' }} del proyecto?"
                    class="text-2xs font-semibold text-red-500 hover:text-red-600">
                    Quitar
                </button>
            </div>
        @empty
            <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-6">Aún no hay participantes en este proyecto.</p>
        @endforelse
    </div>
</div>
