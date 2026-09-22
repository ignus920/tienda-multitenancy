<div>
    <!-- Encabezado y Acción de Clonar -->
    <div class="flex items-center justify-between mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
        <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center select-none">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Formulario Dinámico del Ítem
        </h4>
        @if(count($availableItems) > 0)
        <div class="flex items-center gap-2">
            <select wire:model="cloneItemId" class="text-xs border-gray-300 dark:border-gray-600 dark:bg-gray-800 rounded-lg py-1 pl-2 pr-6">
                <option value="">Copiar estructura de otro ítem...</option>
                @foreach($availableItems as $ai)
                <option value="{{ $ai['id'] }}">{{ $ai['name'] }} ({{ $ai['sku'] }})</option>
                @endforeach
            </select>
            <button type="button" wire:click="cloneAttributes" wire:loading.attr="disabled" class="px-3 py-1 bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-400 text-xs font-bold rounded-lg transition-colors">
                Copiar
            </button>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Columna Izquierda: Formulario Dinámico -->
        <div class="space-y-4">
            @if(count($dynamicFields) === 0)
            <div class="text-center py-6 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">Este ítem no tiene campos personalizados aún.</p>
                <p class="text-xs text-gray-400 mt-1">Usa el panel de la derecha para agregar campos o copia la estructura de otro ítem importado.</p>
            </div>
            @else
                @foreach($dynamicFields as $index => $attr)
                <div class="relative group p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:border-indigo-300 transition-colors">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">{{ $attr['label'] }}</label>
                    
                    @if($attr['field_type'] === 'text')
                        <input type="text" wire:model="dynamicFields.{{ $index }}.value" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @elseif($attr['field_type'] === 'textarea')
                        <textarea wire:model="dynamicFields.{{ $index }}.value" rows="2" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    @elseif($attr['field_type'] === 'select')
                        <select wire:model="dynamicFields.{{ $index }}.value" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Seleccione una opción...</option>
                            @foreach($attr['options_array'] as $opt)
                                <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                        </select>
                    @endif

                    <button type="button" wire:click="deleteField({{ $attr['id'] }})" title="Eliminar campo" class="absolute -top-2 -right-2 bg-red-100 text-red-600 hover:bg-red-500 hover:text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-all shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                @endforeach
                
                <div class="mt-4 flex justify-end">
                    <button type="button" wire:click="save" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg shadow-sm hover:bg-indigo-700 transition-colors">
                        Guardar Valores
                    </button>
                </div>
            @endif
        </div>

        <!-- Columna Derecha: Creador de Campos -->
        <div>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                <h5 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-3">Agregar Nuevo Campo</h5>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nombre del campo *</label>
                        <input type="text" wire:model="newLabel" placeholder="Ej. Material de la suela" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                        @error('newLabel') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Tipo de campo *</label>
                        <select wire:model.live="newType" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm">
                            <option value="text">Texto corto (Fijo)</option>
                            <option value="textarea">Texto largo (Párrafo)</option>
                            <option value="select">Lista desplegable (Select)</option>
                        </select>
                        @error('newType') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    @if($newType === 'select')
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Opciones (separadas por coma) *</label>
                        <textarea wire:model="newOptions" placeholder="Ej. Cuero, Plástico, Goma" rows="2" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg text-sm"></textarea>
                    </div>
                    @endif

                    <div class="pt-2">
                        <button type="button" wire:click="addField" class="w-full px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800 text-sm font-bold rounded-lg transition-colors">
                            + Crear Campo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
