<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-4 sm:p-6">
    <div class="mx-auto max-w-5xl">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Permisos por Perfil</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Definí qué puede hacer cada perfil en cada módulo. Estos permisos son la base;
                a un usuario puntual se le pueden ajustar excepciones desde su formulario.
            </p>
        </div>

        {{-- Selector de perfil --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Perfil</label>
            <select wire:model.live="selectedProfileId"
                    class="w-full sm:w-80 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">— Seleccioná un perfil —</option>
                @foreach($profiles as $profile)
                    <option value="{{ $profile->id }}">{{ $profile->name }}</option>
                @endforeach
            </select>
        </div>

        @if($selectedProfileId && count($matrix))
            <div class="mt-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                                <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Módulo</th>
                                @foreach($actions as $key => $label)
                                    <th class="py-3 px-3 font-semibold text-gray-600 dark:text-gray-300 text-center">
                                        <button type="button" wire:click="toggleColumn('{{ $key }}')"
                                                class="inline-flex items-center gap-1 hover:text-indigo-600 dark:hover:text-indigo-400"
                                                title="Marcar / desmarcar toda la columna">
                                            {{ $label }}
                                            <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h10M4 17h7"/></svg>
                                        </button>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach($matrix as $id => $row)
                                <tr wire:key="perm-{{ $id }}" class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                                    <td class="py-2.5 px-4 font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                                    @foreach($actions as $key => $label)
                                        <td class="py-2.5 px-3 text-center">
                                            <input type="checkbox"
                                                   wire:model="matrix.{{ $id }}.{{ $key }}"
                                                   class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-200 dark:border-gray-700 p-4">
                    <button type="button" wire:click="$set('selectedProfileId', '')"
                            class="px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancelar
                    </button>
                    <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                            class="px-5 py-2 text-sm font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">Guardar permisos</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                Nota: un módulo solo cambia lo que se ve/hace en el sistema cuando el código lo verifica.
                Hoy se aplica «Ver»; «Crear / Editar / Desactivar» se irán aplicando módulo por módulo.
            </p>
        @elseif($selectedProfileId)
            <div class="mt-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-10 text-center text-sm text-gray-400">
                No hay módulos activos en el catálogo de permisos.
            </div>
        @endif
    </div>
</div>
