<div class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Instructivos</h1>
                <p class="text-gray-600 dark:text-slate-400 text-sm mt-1">Bitácoras de referencia por departamento. Tu(s) departamento(s) aparecen primero.</p>
            </div>
            @if($canManage)
            <button wire:click="openCreateDept" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                <x-heroicon-o-plus class="w-4 h-4" />
                Nuevo departamento
            </button>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 border border-gray-200 dark:border-slate-700 transition-colors">
        @if($departments->isEmpty())
            <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                <p>Todavía no hay departamentos creados.</p>
            </div>
        @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($departments as $dept)
            <div class="relative group">
                <a href="{{ route('tenant.instructivos.department', $dept->id) }}" wire:navigate
                    class="flex flex-col items-center justify-center aspect-square rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-md hover:-translate-y-0.5 transition-all p-4 text-center">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl mb-2" style="background-color: {{ $dept->color }}22;">
                        <span>{{ $dept->icon }}</span>
                    </div>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $dept->name }}</span>
                    @if(in_array($dept->id, $myDeptIds))
                        <span class="mt-1 text-[10px] uppercase tracking-wide font-semibold text-indigo-600 dark:text-indigo-400">Mi departamento</span>
                    @endif
                </a>
                @if($canManage)
                <button wire:click="openEditDept({{ $dept->id }})"
                    class="absolute top-1.5 right-1.5 p-1 rounded-full bg-white/90 dark:bg-gray-900/90 text-gray-500 hover:text-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity shadow"
                    title="Editar departamento">
                    <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                </button>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Modal crear/editar departamento --}}
    @if($showDeptModal)
    <div wire:key="dept-modal" x-data x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">
                {{ $editingDeptId ? 'Editar departamento' : 'Nuevo departamento' }}
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre</label>
                    <input type="text" wire:model="deptName" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                    @error('deptName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ícono (emoji)</label>
                        <input type="text" wire:model="deptIcon" maxlength="10" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">
                            Windows: tecla <span class="font-semibold">Windows + .</span><br>
                            Mac: <span class="font-semibold">Cmd + Ctrl + Espacio</span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                        <input type="color" wire:model="deptColor" class="w-full h-9 rounded-lg border-gray-300 dark:border-gray-600">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6">
                @if($editingDeptId)
                <button wire:click="
                    Swal.fire({
                        title: '¿Desactivar departamento?',
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
                            $wire.deactivateDept({{ $editingDeptId }})
                        }
                    })
                " type="button" class="text-sm text-red-600 hover:text-red-700 font-medium">
                    Desactivar
                </button>
                @else
                <span></span>
                @endif
                <div class="flex gap-2">
                    <button wire:click="$set('showDeptModal', false)" type="button" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">Cancelar</button>
                    <button wire:click="saveDept" type="button" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
