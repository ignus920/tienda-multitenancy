<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('tenant.instructivos') }}" wire:navigate class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                <x-heroicon-o-arrow-left class="w-5 h-5" />
            </a>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl" style="background-color: {{ $department->color }}22;">
                {{ $department->icon }}
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $department->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Instructivos de este departamento</p>
            </div>
        </div>
        @if($canManage)
        <div class="flex gap-2">
            <button wire:click="openMembers" class="inline-flex items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors">
                <x-heroicon-o-users class="w-4 h-4" />
                Miembros
            </button>
            <button wire:click="openNew" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <x-heroicon-o-plus class="w-4 h-4" />
                Nuevo instructivo
            </button>
        </div>
        @endif
    </div>

    @if($instructivos->isEmpty())
        <div class="text-center py-16 text-gray-500 dark:text-gray-400">
            <p>Todavía no hay instructivos en este departamento.</p>
        </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($instructivos as $instructivo)
        <div class="relative group bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-md transition-shadow">
            <a href="{{ route('tenant.instructivos.show', $instructivo->id) }}" wire:navigate class="block">
                <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-1">{{ $instructivo->title }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $instructivo->entries_count }} {{ \Illuminate\Support\Str::plural('entrada', $instructivo->entries_count) }}
                    · por {{ $instructivo->author->name ?? '—' }}
                </p>
            </a>
            @if($canManage)
            <button wire:click="
                Swal.fire({
                    title: '¿Desactivar instructivo?',
                    text: 'Dejará de verse en el listado. No se elimina la información.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#4f46e5',
                    confirmButtonText: 'Sí, desactivar',
                    cancelButtonText: 'Cancelar',
                    background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $wire.deactivateInstructivo({{ $instructivo->id }})
                    }
                })
            " class="absolute top-2 right-2 p-1 rounded-full bg-white/90 dark:bg-gray-900/90 text-gray-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition-opacity shadow"
                title="Desactivar">
                <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
            </button>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Modal nuevo instructivo --}}
    @if($showNewModal)
    <div wire:key="new-instructivo-modal" x-data x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Nuevo instructivo</h3>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título</label>
            <input type="text" wire:model="newTitle" wire:keydown.enter="saveNew" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
            @error('newTitle') <span class="text-xs text-red-500">{{ $message }}</span> @enderror

            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showNewModal', false)" type="button" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Cancelar</button>
                <button wire:click="saveNew" type="button" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Crear</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal miembros del departamento --}}
    @if($showMembersModal)
    <div wire:key="members-modal" x-data x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Miembros de {{ $department->name }}</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Estos usuarios verán este departamento primero en su pantalla de Instructivos.</p>

            <div class="max-h-64 overflow-y-auto space-y-1 border border-gray-200 dark:border-gray-700 rounded-lg p-2">
                @foreach($this->tenantUsers as $user)
                <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700 text-sm text-gray-700 dark:text-gray-200">
                    <input type="checkbox" wire:model="memberUserIds" value="{{ $user->id }}" class="rounded border-gray-300 text-indigo-600">
                    {{ $user->name }}
                </label>
                @endforeach
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button wire:click="$set('showMembersModal', false)" type="button" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Cancelar</button>
                <button wire:click="saveMembers" type="button" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Guardar</button>
            </div>
        </div>
    </div>
    @endif
</div>
