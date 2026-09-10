<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-4 sm:p-6">
    <div class="mx-auto max-w-4xl">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Permisos por Perfil</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Cada módulo del menú es un grupo. Abrilo para marcar solo las subsecciones que este perfil debe ver.
                A un usuario puntual se le pueden ajustar excepciones desde su formulario.
            </p>
        </div>

        {{-- Selector de perfil + buscador --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm
                    flex flex-col sm:flex-row sm:items-end gap-3"
             x-data="{ q: '' }">
            <div class="sm:w-72">
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1.5">Perfil</label>
                <select wire:model.live="selectedProfileId"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Seleccioná un perfil —</option>
                    @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}">{{ $profile->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($selectedProfileId && count($groups))
            <div class="flex-1">
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500 mb-1.5">Buscar módulo</label>
                <input type="search" x-model="q"
                       @input="$dispatch('perm-search', { q: q.toLowerCase().trim() })"
                       placeholder="Ej: bodegas, ítems, cartera…"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex gap-2">
                <button type="button" @click="$dispatch('perm-toggle-all', { open: true })"
                        class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    Expandir todo
                </button>
                <button type="button" @click="$dispatch('perm-toggle-all', { open: false })"
                        class="px-3 py-2 text-xs font-medium rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                    Colapsar todo
                </button>
            </div>
            @endif
        </div>

        @if($selectedProfileId && count($groups))
            @php $gridStyle = 'display:grid;grid-template-columns:minmax(0,1fr) 56px 56px 56px 56px;align-items:center;'; @endphp

            {{-- Encabezado de columnas --}}
            <div class="sticky top-0 z-10 mt-4 bg-gray-50 dark:bg-gray-900 py-2.5 pl-4 pr-3 border-b border-gray-200 dark:border-gray-700"
                 style="{{ $gridStyle }}">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Módulo</span>
                @foreach($actions as $key => $label)
                    <span class="text-center text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $label }}</span>
                @endforeach
            </div>

            <div class="mt-2 space-y-2">
                @foreach($groups as $grp)
                    @php
                        $ids = collect($grp['perms'])->pluck('id')->all();
                        $totalG = count($ids);
                        $counts = [];
                        foreach ($actions as $ak => $al) {
                            $counts[$ak] = collect($ids)->filter(fn ($id) => !empty($matrix[$id][$ak] ?? false))->count();
                        }
                        $onShow = $counts['show'];
                        if ($onShow === 0)            { $chipCls = 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'; $chipTxt = 'Sin acceso'; }
                        elseif ($onShow === $totalG)  { $chipCls = 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300'; $chipTxt = 'Completo'; }
                        else                          { $chipCls = 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300'; $chipTxt = 'Parcial'; }
                        $searchStr = strtolower($grp['title'] . ' ' . collect($grp['perms'])->pluck('label')->implode(' '));
                    @endphp

                    <div wire:key="grp-{{ $grp['key'] }}"
                         x-data="{ open: {{ $grp['title'] === 'Inventario' ? 'true' : 'false' }} }"
                         data-search="{{ $searchStr }}"
                         @perm-search.window="$el.hidden = $event.detail.q !== '' && !$el.dataset.search.includes($event.detail.q)"
                         @perm-toggle-all.window="open = $event.detail.open"
                         class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">

                        {{-- Cabecera del grupo --}}
                        <div class="pl-3 pr-3" style="{{ $gridStyle }}">
                            @if($grp['single'])
                                <div class="flex items-center gap-2 min-w-0 py-2.5 pl-7">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $grp['title'] }}</span>
                                    <x-permission-menu-hint :name="$grp['perms'][0]['name']" />
                                </div>
                            @else
                                <button type="button" @click="open = !open"
                                        class="flex items-center gap-2 min-w-0 py-3 text-left w-full">
                                    <svg class="w-4 h-4 shrink-0 text-gray-400 transition-transform" :class="open && 'rotate-90 text-indigo-500'"
                                         fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $grp['title'] }}</span>
                                    <x-permission-menu-hint :name="$grp['title']" />
                                    <span class="hidden sm:inline text-xs font-mono text-gray-400 dark:text-gray-500">{{ $onShow }}/{{ $totalG }}</span>
                                    <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full {{ $chipCls }}">{{ $chipTxt }}</span>
                                </button>
                            @endif

                            @foreach($actions as $ak => $al)
                                <div class="flex justify-center">
                                    @if($grp['single'])
                                        <input type="checkbox"
                                               wire:model.live="matrix.{{ $grp['perms'][0]['id'] }}.{{ $ak }}"
                                               class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                    @else
                                        @php
                                            $cOn = $counts[$ak];
                                            $stateCls = $cOn === 0
                                                ? 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'
                                                : ($cOn === $totalG ? 'border-indigo-600 bg-indigo-600' : 'border-indigo-500 bg-indigo-100 dark:bg-indigo-900/50');
                                        @endphp
                                        <button type="button"
                                                wire:click="toggleGroupColumn(@js($ids), '{{ $ak }}')"
                                                title="{{ $al }} — marcar/desmarcar todo {{ $grp['title'] }}"
                                                class="h-4 w-4 rounded border-2 flex items-center justify-center hover:border-indigo-500 {{ $stateCls }}">
                                            @if($cOn === $totalG)
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/></svg>
                                            @elseif($cOn > 0)
                                                <span class="block w-2 rounded bg-indigo-600 dark:bg-indigo-300" style="height:2px"></span>
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Subsecciones --}}
                        @unless($grp['single'])
                        <div x-show="open" x-transition.opacity
                             class="border-t border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/30">
                            @foreach($grp['perms'] as $perm)
                                <div wire:key="perm-{{ $perm['id'] }}"
                                     class="pl-10 pr-3 py-2 border-b border-gray-100 dark:border-gray-700/60 last:border-0"
                                     style="{{ $gridStyle }}">
                                    <span class="text-sm text-gray-800 dark:text-gray-200 truncate">{{ $perm['label'] }}</span>
                                    @foreach($actions as $ak => $al)
                                        <div class="flex justify-center">
                                            <input type="checkbox"
                                                   wire:model.live="matrix.{{ $perm['id'] }}.{{ $ak }}"
                                                   class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        @endunless
                    </div>
                @endforeach
            </div>

            {{-- Barra de guardado --}}
            <div class="sticky bottom-0 mt-4 border-t border-gray-200 dark:border-gray-700
                        bg-white/95 dark:bg-gray-800/95 px-4 py-3 flex items-center gap-3 rounded-b-xl">
                <p class="flex-1 text-xs text-gray-500 dark:text-gray-400">
                    Hoy se aplica «Ver» en los menús. «Crear / Editar / Desactivar» se van activando módulo por módulo.
                </p>
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

        @elseif($selectedProfileId)
            <div class="mt-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 p-10 text-center text-sm text-gray-400">
                No hay módulos activos en el catálogo de permisos.
            </div>
        @endif
    </div>
</div>
