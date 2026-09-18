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
                    @if($materialRequest->status === 'salida_generada')
                        Historial de la solicitud
                        <span class="ml-2 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 normal-case font-semibold">Salida generada</span>
                    @else
                        Solicitud actual
                        @if($materialRequest->status === 'revisada')
                            <span class="ml-2 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 normal-case font-semibold">Enviada a Importaciones</span>
                        @else
                            <span class="ml-2 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 normal-case font-semibold">Pendiente de revisión</span>
                        @endif
                    @endif
                </h3>
            </div>
            @if($materialRequest->status === 'pendiente')
            <p class="text-3xs text-purple-600 dark:text-purple-400">
                Verifica qué materiales ya tienes disponibles en Laboratorio antes de enviar. Las cantidades quedan redondeadas a unidades enteras — ajustar aquí NO cambia la lista de materiales del proyecto, solo lo que le vas a pedir a Bodega.
            </p>
            @elseif($materialRequest->status === 'revisada')
            <p class="text-3xs text-purple-600 dark:text-purple-400">
                Revisión de materiales: esta es la solicitud que Laboratorio ya envió. Verifica las cantidades antes de generar la Salida de Mercancía — ajustar aquí NO cambia la lista de materiales del proyecto.
            </p>
            @else
            <p class="text-3xs text-purple-600 dark:text-purple-400">
                Esta solicitud ya se convirtió en Salida de Mercancía (se descontó del ERP y se sincronizó con Alegra) — quedó registrada como historial, ya no se puede editar ni volver a enviar.
                @if($materialRequest->requestedBy)
                    Enviada por {{ $materialRequest->requestedBy->name }} el {{ $materialRequest->updated_at->format('d/m/Y H:i') }}.
                @endif
            </p>
            @endif

            @if($materialRequest->status === 'pendiente' && $hasPendingUpdates)
            <div class="flex items-center gap-2 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/60 rounded-lg p-3 text-3xs text-amber-700 dark:text-amber-400">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span>La Lista de Materiales cambió mientras tenías esta pantalla abierta — actualiza antes de seguir editando o enviar.</span>
            </div>
            @endif

            <div class="space-y-1.5">
                @foreach($materialRequest->items as $item)
                    <div class="flex items-center justify-between gap-3 bg-white dark:bg-gray-850 rounded-lg px-3 py-2 {{ $item->is_removed ? 'opacity-50' : '' }}">
                        <span class="text-xs text-gray-800 dark:text-gray-200 flex-1 {{ $item->is_removed ? 'line-through' : '' }}">{{ $item->description }}</span>
                        @if($materialRequest->status === 'pendiente' && $canManage && !$hasPendingUpdates)
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
                @if($hasPendingUpdates)
                    <button wire:click="refreshMaterialsList" type="button"
                        class="px-4 py-1.5 text-2xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow transition-colors flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        Actualizar Lista
                    </button>
                @else
                    <button type="button"
                        x-data="{ items: @js($materialRequest->items->where('is_removed', false)->values()->map(fn ($i) => ['description' => $i->description, 'quantity' => $i->quantity_requested])) }"
                        @click="
                            const rows = items.map((item) => '<tr><td style=\'padding:6px 8px;text-align:left;border-bottom:1px solid #f3f4f6;\'>' + item.description + '</td><td style=\'padding:6px 8px;text-align:right;font-weight:700;border-bottom:1px solid #f3f4f6;\'>' + item.quantity + '</td></tr>').join('');
                            Swal.fire({
                                title: '¿Enviar esta solicitud a Importaciones?',
                                html: '<div class=\'mt-2 text-left\'><p class=\'text-xs text-gray-500 dark:text-gray-400 mb-3\'>Verifica la lista antes de confirmar — esto es lo que se le va a pedir a Bodega:</p><div style=\'max-height:280px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;\'><table style=\'width:100%;font-size:13px;border-collapse:collapse;\'><thead><tr style=\'background:#f9fafb;\'><th style=\'padding:6px 8px;text-align:left;color:#6b7280;font-size:11px;text-transform:uppercase;\'>Producto</th><th style=\'padding:6px 8px;text-align:right;color:#6b7280;font-size:11px;text-transform:uppercase;\'>Cantidad</th></tr></thead><tbody>' + rows + '</tbody></table></div></div>',
                                showCancelButton: true,
                                confirmButtonText: 'Sí, enviar a Importaciones',
                                cancelButtonText: 'Cancelar',
                                confirmButtonColor: '#7c3aed',
                                cancelButtonColor: '#6b7280',
                                customClass: { popup: 'rounded-xl dark:bg-slate-900', title: 'text-lg font-bold text-gray-900 dark:text-white' }
                            }).then((result) => { if (result.isConfirmed) { $wire.markRequestReviewed() } })
                        "
                        class="px-4 py-1.5 text-2xs font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-lg shadow transition-colors">
                        Enviar a Importaciones
                    </button>
                @endif
            </div>
            @elseif($materialRequest->status === 'pendiente' && $canManageOutbound)
                <div class="flex items-center justify-between gap-2 pt-2">
                    <p class="text-3xs text-gray-500 dark:text-gray-400">
                        Laboratorio todavía está ajustando esta solicitud — se notificará a Importaciones cuando la envíen.
                    </p>
                    @if($hasPendingUpdates)
                        <button wire:click="refreshMaterialsList" type="button"
                            class="shrink-0 px-3 py-1.5 text-2xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow transition-colors flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            Actualizar Lista
                        </button>
                    @endif
                </div>
            @endif

            @if($materialRequest->status === 'revisada' && $canManageOutbound)
            <div class="flex items-center justify-between gap-3 pt-3 border-t border-purple-200 dark:border-purple-800/60">
                <p class="text-3xs text-purple-600 dark:text-purple-400">
                    Revisa las cantidades y genera la salida — descuenta el stock del ERP y sincroniza con Alegra.
                </p>
                <button type="button"
                    @click="
                        Swal.fire({
                            title: '¿Generar la Salida de Mercancía?',
                            text: 'Se descontará del ERP y se sincronizará con Alegra.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#10b981',
                            confirmButtonText: 'Sí, generar salida',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (!result.isConfirmed) return;
                            Swal.fire({
                                title: 'Generando Salida de Mercancía...',
                                html: 'Sincronizando con el ERP y Alegra — esto puede tardar unos segundos, no cierres esta ventana.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => { Swal.showLoading() }
                            });
                            $wire.generateOutboundMovement({{ $materialRequest->id }}).then(() => { Swal.close() });
                        })
                    "
                    class="shrink-0 px-4 py-1.5 text-2xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow transition-colors">
                    Generar Salida de Mercancía
                </button>
            </div>
            @endif
        </div>
        @endif
    @endif
</div>
