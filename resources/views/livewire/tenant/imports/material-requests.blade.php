<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Solicitudes de Materiales</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Solicitudes enviadas por Laboratorio desde sus proyectos, listas para generar la Salida de Mercancía.</p>
        </div>

        @if(!$canManageRequests)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-10 text-center text-sm text-gray-400">
                No tienes acceso a esta sección.
            </div>
        @else
            <div class="space-y-4">
                @forelse($requests as $request)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-3">
                            <div>
                                <h2 class="text-sm font-bold text-gray-900 dark:text-white">
                                    Proyecto: {{ $request->project->title ?? 'Proyecto #' . $request->project_id }}
                                </h2>
                                <p class="text-2xs text-gray-400">
                                    Solicitado por {{ $request->requestedBy->name ?? 'Usuario' }} · {{ $request->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <a href="{{ route('tenant.projects.workspace', ['id' => $request->project_id]) }}" wire:navigate
                                class="text-2xs font-semibold text-indigo-600 hover:text-indigo-700">
                                Ver proyecto →
                            </a>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 pt-3 space-y-1.5">
                            @foreach($request->items as $item)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-700 dark:text-gray-300">{{ $item->description }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $item->quantity_requested }} un.</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="button"
                                wire:click="generateOutboundMovement({{ $request->id }})"
                                wire:confirm="¿Generar la Salida de Mercancía para este proyecto? Esto descontará el inventario y sincronizará con Alegra."
                                wire:loading.attr="disabled" wire:target="generateOutboundMovement({{ $request->id }})"
                                class="px-4 py-2 text-xs font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-lg shadow transition-colors disabled:opacity-50">
                                Generar Salida de Mercancía
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-10 text-center text-sm text-gray-400">
                        No hay solicitudes de materiales pendientes de revisión.
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</div>
