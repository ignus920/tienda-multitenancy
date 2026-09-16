<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 space-y-4">
    <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Solicitud de Materiales</h2>

    @if(!$canManage && !$canManageOutbound)
        <p class="text-xs text-gray-400 text-center py-10">No tienes acceso a esta sección.</p>
    @else
        @if(!$materialRequest)
            @if($canManage && !$isClosed && $hasActiveErpMaterials)
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Genera la solicitud a partir de los materiales del ERP de la pestaña "Materiales", redondeados a unidades enteras hacia arriba, para pedirle a Bodega lo que haga falta.
                </p>
                <button wire:click="generateMaterialRequest" type="button"
                    class="px-4 py-2 text-xs font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-lg shadow transition-colors">
                    Generar Solicitud de Materiales
                </button>
            @elseif($canManage)
                <p class="text-xs text-gray-400 text-center py-10">
                    Aún no hay materiales del ERP en este proyecto para generar una solicitud. Agrégalos primero en la pestaña "Materiales".
                </p>
            @else
                <p class="text-xs text-gray-400 text-center py-10">
                    No hay ninguna solicitud de materiales pendiente de revisión en este proyecto.
                </p>
            @endif
        @else
        <div class="bg-purple-50 dark:bg-purple-900/10 border border-purple-200 dark:border-purple-800/60 rounded-lg p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-2xs font-bold text-purple-700 dark:text-purple-400 uppercase tracking-wider">
                    Solicitud actual
                    @if($materialRequest->status === 'revisada')
                        <span class="ml-2 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 normal-case font-semibold">Enviada a Importaciones</span>
                    @else
                        <span class="ml-2 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 normal-case font-semibold">Pendiente de revisión</span>
                    @endif
                </h3>
            </div>
            @if($materialRequest->status === 'pendiente')
            <p class="text-3xs text-purple-600 dark:text-purple-400">
                Verifica qué materiales ya tienes disponibles en Laboratorio antes de enviar. Las cantidades quedan redondeadas a unidades enteras — ajustar aquí NO cambia la lista de materiales del proyecto, solo lo que le vas a pedir a Bodega.
            </p>
            @else
            <p class="text-3xs text-purple-600 dark:text-purple-400">
                Revisión de materiales: esta es la solicitud que Laboratorio ya envió. Verifica las cantidades antes de generar la Salida de Mercancía — ajustar aquí NO cambia la lista de materiales del proyecto.
            </p>
            @endif

            <div class="space-y-1.5">
                @foreach($materialRequest->items as $item)
                    <div class="flex items-center justify-between gap-3 bg-white dark:bg-gray-850 rounded-lg px-3 py-2 {{ $item->is_removed ? 'opacity-50' : '' }}">
                        <span class="text-xs text-gray-800 dark:text-gray-200 flex-1 {{ $item->is_removed ? 'line-through' : '' }}">{{ $item->description }}</span>
                        @if($materialRequest->status === 'pendiente' && $canManage)
                            <input type="number" min="1" step="1" value="{{ $item->quantity_requested }}"
                                wire:change="updateRequestItemQuantity({{ $item->id }}, $event.target.value)"
                                @if($item->is_removed) disabled @endif
                                class="w-16 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded px-2 py-1 text-xs text-right">
                            <button type="button" wire:click="toggleRequestItemRemoved({{ $item->id }})"
                                class="text-2xs font-semibold {{ $item->is_removed ? 'text-emerald-600 hover:text-emerald-700' : 'text-red-500 hover:text-red-600' }}">
                                {{ $item->is_removed ? 'Restaurar' : 'Quitar' }}
                            </button>
                        @else
                            <span class="w-16 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $item->quantity_requested }}</span>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($materialRequest->status === 'pendiente' && $canManage)
            <div class="flex justify-end gap-2 pt-2">
                <button wire:click="cancelMaterialRequest" wire:confirm="¿Cancelar esta solicitud de materiales?" type="button"
                    class="px-3 py-1.5 text-2xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    Cancelar Solicitud
                </button>
                <button wire:click="markRequestReviewed" type="button"
                    class="px-4 py-1.5 text-2xs font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-lg shadow transition-colors">
                    Enviar a Importaciones
                </button>
            </div>
            @elseif($materialRequest->status === 'pendiente' && $canManageOutbound)
                <p class="text-3xs text-gray-500 dark:text-gray-400 text-center pt-2">
                    Laboratorio todavía está ajustando esta solicitud — se notificará a Importaciones cuando la envíen.
                </p>
            @endif

            @if($materialRequest->status === 'revisada' && $canManageOutbound)
            <div class="flex items-center justify-between gap-3 pt-3 border-t border-purple-200 dark:border-purple-800/60">
                <p class="text-3xs text-purple-600 dark:text-purple-400">
                    Revisa las cantidades y genera la salida — descuenta el stock del ERP y sincroniza con Alegra.
                </p>
                <button wire:click="generateOutboundMovement({{ $materialRequest->id }})"
                    wire:confirm="¿Generar la Salida de Mercancía para esta solicitud? Se descontará del ERP y de Alegra."
                    type="button"
                    class="shrink-0 px-4 py-1.5 text-2xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow transition-colors">
                    Generar Salida de Mercancía
                </button>
            </div>
            @endif
        </div>
        @endif
    @endif
</div>
