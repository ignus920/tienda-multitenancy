<div class="p-6 bg-gray-50 dark:bg-slate-900 min-h-screen transition-colors">
    <!-- Encabezado -->
    <div class="bg-white dark:bg-slate-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-slate-700 transition-colors">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Cartera</h1>
                <p class="text-gray-600 dark:text-slate-400 text-sm mt-1">Gestión de autorizaciones</p>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <!-- Despacho -->
        <div wire:click="setFilter('despacho')"
             style="{{ $activeFilter === 'despacho' ? 'background:#F59E0B;border-color:#D97706;' : '' }}"
             class="p-4 rounded-lg border transition-all duration-300 cursor-pointer hover:shadow-lg {{ $activeFilter === 'despacho' ? 'border-amber-600' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 shadow-sm' }}">
            <div class="flex items-center space-x-4">
                <div style="background:{{ $activeFilter === 'despacho' ? 'rgba(255,255,255,0.2)' : '#F59E0B' }};"
                     class="w-12 h-12 rounded-lg flex items-center justify-center text-xl font-bold text-white">
                    {{ $metrics['despacho'] }}
                </div>
                <p class="text-sm font-semibold {{ $activeFilter === 'despacho' ? 'text-white' : 'text-gray-500 dark:text-slate-400' }}">Despacho</p>
            </div>
        </div>
        <!-- Pago -->
        <div wire:click="setFilter('pago')"
             style="{{ $activeFilter === 'pago' ? 'background:#4F46E5;border-color:#4338CA;' : '' }}"
             class="p-4 rounded-lg border transition-all duration-300 cursor-pointer hover:shadow-lg {{ $activeFilter === 'pago' ? 'border-indigo-700' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 shadow-sm' }}">
            <div class="flex items-center space-x-4">
                <div style="background:{{ $activeFilter === 'pago' ? 'rgba(255,255,255,0.2)' : '#4F46E5' }};"
                     class="w-12 h-12 rounded-lg flex items-center justify-center text-xl font-bold text-white">
                    {{ $metrics['pago'] }}
                </div>
                <p class="text-sm font-semibold {{ $activeFilter === 'pago' ? 'text-white' : 'text-gray-500 dark:text-slate-400' }}">Pago</p>
            </div>
        </div>
        <!-- Empaque -->
        <div wire:click="setFilter('empaque')"
             style="{{ $activeFilter === 'empaque' ? 'background:#22C55E;border-color:#16A34A;' : '' }}"
             class="p-4 rounded-lg border transition-all duration-300 cursor-pointer hover:shadow-lg {{ $activeFilter === 'empaque' ? 'border-green-600' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 shadow-sm' }}">
            <div class="flex items-center space-x-4">
                <div style="background:{{ $activeFilter === 'empaque' ? 'rgba(255,255,255,0.2)' : '#22C55E' }};"
                     class="w-12 h-12 rounded-lg flex items-center justify-center text-xl font-bold text-white">
                    {{ $metrics['empaque'] }}
                </div>
                <p class="text-sm font-semibold {{ $activeFilter === 'empaque' ? 'text-white' : 'text-gray-500 dark:text-slate-400' }}">Empaque</p>
            </div>
        </div>
        <!-- Anulados -->
        <div wire:click="setFilter('anulados')"
             style="{{ $activeFilter === 'anulados' ? 'background:#EF4444;border-color:#DC2626;' : '' }}"
             class="p-4 rounded-lg border transition-all duration-300 cursor-pointer hover:shadow-lg {{ $activeFilter === 'anulados' ? 'border-red-600' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 shadow-sm' }}">
            <div class="flex items-center space-x-4">
                <div style="background:{{ $activeFilter === 'anulados' ? 'rgba(255,255,255,0.2)' : '#EF4444' }};"
                     class="w-12 h-12 rounded-lg flex items-center justify-center text-xl font-bold text-white">
                    {{ $metrics['anulados'] }}
                </div>
                <p class="text-sm font-semibold {{ $activeFilter === 'anulados' ? 'text-white' : 'text-gray-500 dark:text-slate-400' }}">Anulados</p>
            </div>
        </div>
        <!-- Pendientes -->
        <div wire:click="setFilter('pendientes')"
             style="{{ $activeFilter === 'pendientes' ? 'background:#8B5CF6;border-color:#7C3AED;' : '' }}"
             class="p-4 rounded-lg border transition-all duration-300 cursor-pointer hover:shadow-lg {{ $activeFilter === 'pendientes' ? 'border-violet-600' : 'bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-700 shadow-sm' }}">
            <div class="flex items-center space-x-4">
                <div style="background:{{ $activeFilter === 'pendientes' ? 'rgba(255,255,255,0.2)' : '#8B5CF6' }};"
                     class="w-12 h-12 rounded-lg flex items-center justify-center text-xl font-bold text-white">
                    {{ $metrics['pendientes'] }}
                </div>
                <p class="text-sm font-semibold {{ $activeFilter === 'pendientes' ? 'text-white' : 'text-gray-500 dark:text-slate-400' }}">Pendientes</p>
            </div>
        </div>
    </div>

    <!-- Buscador y Filtros -->
    <div class="bg-white dark:bg-slate-800 p-4 rounded-t-xl border border-gray-100 dark:border-slate-700">
        <div style="display: flex; align-items: flex-end; gap: 12px;">
            <div style="flex: 1.5; min-width: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Búsqueda rápida</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Búsqueda rápida.." class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm pl-10">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div style="flex: 1; min-width: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">NIT / Cédula</label>
                <input type="text" wire:model.live.debounce.300ms="searchNit" placeholder="Ej: 900.." class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
            </div>
            <div style="flex: 1; min-width: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nombre / Razón Social</label>
                <input type="text" wire:model.live.debounce.300ms="searchName" placeholder="Buscar cliente..." class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
            </div>
            <div style="flex: 1; min-width: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Número Cotización</label>
                <input type="text" wire:model.live.debounce.300ms="searchQuote" placeholder="Ej: COT-123" class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
            </div>
            <div style="flex: 1; min-width: 0;">
                <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-1">desde:</label>
                <input type="date" wire:model.live="fromDate" class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
            </div>
            <div style="flex: 1; min-width: 0;">
                <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-1">hasta:</label>
                <div style="display: flex; gap: 6px; align-items: center;">
                    <input type="date" wire:model.live="toDate" class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
                    <button wire:click="clearFilters" class="p-2 text-gray-400 hover:text-red-500 transition-colors flex-shrink-0" title="Limpiar Filtros">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div style="flex-shrink: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Mostrar</label>
                <select wire:model.live="perPage" class="rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 text-sm py-2">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <!-- Segunda Fila de Filtros (Confirmaciones) -->
        <div class="mt-4" style="display: flex; align-items: flex-end; gap: 12px;">
            <div style="flex: 1; min-width: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Confirmacion de despacho</label>
                <select wire:model.live="statusDispatch" class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
                    <option value="">Seleccione una opcion</option>
                    <option value="1">Confirmada</option>
                    <option value="0">No confirmada</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 0;">
                <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Confirmacion de pago</label>
                <select wire:model.live="statusPayment" class="w-full rounded-lg border-gray-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white text-sm">
                    <option value="">Seleccione una opcion</option>
                    <option value="1">Confirmada</option>
                    <option value="0">No confirmada</option>
                </select>
            </div>
            <!-- Espaciador para empujar los filtros a la izquierda -->
            <div style="flex: 4;"></div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="mt-6 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700 overflow-hidden transition-colors">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-slate-900/50 border-b border-gray-100 dark:border-slate-700">
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">#OP / Cliente</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Estado</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Tipo Entrega</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Retenciones</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Formas de pago</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Empaque</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Despacho</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Pago</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">ACCIONES</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                @foreach($remissions as $remission)
                @php
                    $empaque = $remission->authorizations->where('auth_type', 'empaque')->last();
                    $despacho = $remission->authorizations->where('auth_type', 'despacho')->last();
                    $pago = $remission->authorizations->where('auth_type', 'pago')->last();
                    $totalConFlete = $remission->sub_total_rem + ($remission->flete ?? 0);
                @endphp
                <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">#{{ $remission->consecutive }}</span>
                            <span class="text-xs text-gray-400 mb-1">{{ $remission->created_at->format('Y-m-d H:i') }}</span>
                            <span class="text-sm font-medium text-gray-700 dark:text-slate-300 uppercase">{{ $remission->quote->customer_name ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase 
                            {{ $remission->status === 'ANULADO' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600' }}">
                            {{ $remission->status }}
                        </span>
                    </td>

                    {{-- Columna Tipo de Entrega --}}
                    <td class="px-6 py-4">
                        @if($remission->deliveryTypeModel)
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-900/30 border border-sky-200 dark:border-sky-800 px-2 py-1 rounded-lg">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    {{ $remission->deliveryTypeModel->name }}
                                </span>
                            </div>
                        @else
                            <span class="text-[11px] text-gray-400 italic">Sin definir</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="bg-gray-100 dark:bg-slate-900/50 p-2 rounded-lg text-[11px] space-y-1">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal:</span>
                                <span class="text-gray-600">${{ number_format($remission->sub_total_rem, 0) }}</span>
                            </div>
                            @if($remission->flete > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Flete:</span>
                                <span class="text-gray-600">+${{ number_format($remission->flete, 0) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between border-t border-gray-200 dark:border-slate-700 pt-1">
                                <span class="font-semibold text-gray-600">Total:</span>
                                <span class="font-bold text-indigo-600">${{ number_format($totalConFlete, 0) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 dark:border-slate-700 pt-1">
                                <span class="text-gray-500">Rfte: ${{ number_format($remission->invoice->retentionFuente ?? 0, 0) }}</span>
                                <span class="text-gray-500">Rtica: ${{ number_format($remission->invoice->retentionIca ?? 0, 0) }}</span>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Formas de pago -->
                    <td class="px-6 py-4">
                        <div class="flex flex-col gap-2">
                            {{-- Si hay pagos en la factura real (Pagos Recibidos) --}}
                            @if($remission->invoice && $remission->invoice->payments->count() > 0)
                                <p class="text-[9px] font-bold text-gray-400 uppercase mb-0.5">Pagos Recibidos:</p>
                                @foreach($remission->invoice->payments as $payment)
                                    <div class="bg-indigo-50/50 dark:bg-indigo-900/10 p-1.5 rounded border border-indigo-100 dark:border-indigo-900/30 flex flex-col gap-1">
                                        <div>
                                            <p class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 uppercase leading-none mb-1">
                                                {{ $payment->methodPayment->name ?? 'PAGO' }}
                                            </p>
                                            <p class="text-xs font-bold text-gray-700 dark:text-slate-300">
                                                ${{ number_format($payment->value, 0) }}
                                            </p>
                                        </div>
                                        @if($payment->proof_payment || $remission->proof_payment)
                                            <a href="{{ asset('storage/' . ($payment->proof_payment ?? $remission->proof_payment)) }}" 
                                               target="_blank" 
                                               class="inline-flex items-center text-[10px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold mt-0.5 transition-colors gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                </svg>
                                                Ver Soporte
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            {{-- Si hay detalles de pago múltiples en JSON (NUEVO SISTEMA) --}}
                            @elseif(!empty($remission->payment_details))
                                @php
                                    $details = $remission->payment_details;
                                    if (is_string($details)) {
                                        $details = json_decode($details, true);
                                    }
                                @endphp
                                @if(is_array($details) && count($details) > 0)
                                    <p class="text-[9px] font-bold text-gray-400 uppercase mb-0.5">Soportes Cargados:</p>
                                    @foreach($details as $payDetail)
                                         @php
                                             $methodName = $allMethodPayments[$payDetail['method_payment_id'] ?? '']['name'] ?? 'TRANSFERENCIA';
                                         @endphp
                                         <div class="bg-indigo-50/50 dark:bg-indigo-900/10 p-1.5 rounded border border-indigo-100 dark:border-indigo-900/30 flex flex-col gap-1 mb-1.5">
                                             <div>
                                                 <p class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 uppercase leading-none mb-1">
                                                     {{ $methodName }}
                                                 </p>
                                                 <p class="text-xs font-bold text-gray-700 dark:text-slate-300">
                                                     ${{ number_format($payDetail['value'] ?? 0, 0) }}
                                                 </p>
                                             </div>
                                             @if(!empty($payDetail['proof_payment']))
                                                 <a href="{{ asset('storage/' . $payDetail['proof_payment']) }}" 
                                                    target="_blank" 
                                                    class="inline-flex items-center text-[10px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold mt-0.5 transition-colors gap-1"
                                                    title="Ver soporte de pago">
                                                     <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                     </svg>
                                                     Ver Soporte
                                                 </a>
                                             @endif
                                         </div>
                                     @endforeach
                                 @endif
                            {{-- Fallback: Si hay soporte tradicional (REGISTROS HISTÓRICOS) --}}
                            @elseif($remission->proof_payment)
                                <p class="text-[9px] font-bold text-gray-400 uppercase mb-0.5">Soporte Cargado:</p>
                                <div class="bg-indigo-50/50 dark:bg-indigo-900/10 p-1.5 rounded border border-indigo-100 dark:border-indigo-900/30 flex flex-col gap-1">
                                    <div>
                                        <p class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 uppercase leading-none mb-1">
                                            {{ $remission->methodPayment->name ?? 'TRANSFERENCIA' }}
                                        </p>
                                        <p class="text-xs font-bold text-gray-700 dark:text-slate-300">
                                            ${{ number_format($totalConFlete, 0) }}
                                        </p>
                                    </div>

                                    <a href="{{ asset('storage/' . $remission->proof_payment) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center text-[10px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-bold mt-0.5 transition-colors gap-1"
                                       title="Ver soporte de pago de la remisión">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                        Ver Soporte
                                    </a>
                                </div>
                            {{-- Si no hay pagos de ningún tipo --}}
                            @else
                                <div class="mb-1 pb-1 border-b border-gray-100 dark:border-slate-700/50">
                                    <p class="text-[9px] font-bold text-gray-400 uppercase mb-0.5">Acordado:</p>
                                    <div class="flex items-center text-green-600 dark:text-green-400">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-[11px] font-bold uppercase">{{ $remission->methodPayment->name ?? 'SIN DEFINIR' }}</span>
                                    </div>
                                </div>
                                <span class="text-[10px] text-gray-400 italic">Sin pagos registrados</span>
                            @endif

                            {{-- Botón para cargar soporte si no tiene proof_payment --}}
                            @if(!$remission->proof_payment)
                            <button
                                wire:click="openUploadModal({{ $remission->id }})"
                                class="mt-1.5 w-full inline-flex items-center justify-center gap-1 px-2 py-1 rounded-md bg-violet-50 hover:bg-violet-100 dark:bg-violet-900/20 dark:hover:bg-violet-900/40 border border-violet-200 dark:border-violet-800 text-violet-700 dark:text-violet-300 text-[10px] font-bold transition-all duration-200 group"
                                title="Cargar soporte de pago">
                                <svg class="w-3 h-3 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Cargar Soporte
                            </button>
                            @else
                            <button
                                wire:click="openUploadModal({{ $remission->id }})"
                                class="mt-1.5 w-full inline-flex items-center justify-center gap-1 px-2 py-1 rounded-md bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:hover:bg-amber-900/40 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 text-[10px] font-bold transition-all duration-200 group"
                                title="Reemplazar soporte de pago">
                                <svg class="w-3 h-3 group-hover:-translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reemplazar
                            </button>
                            @endif
                        </div>
                    </td>

                    <!-- Checkboxes de Autorización -->
                    <td class="px-6 py-4">
                        <div class="flex flex-col items-center space-y-2">
                            <input type="checkbox" 
                                wire:click="toggleAuthorization({{ $remission->id }}, 'empaque')"
                                {{ $empaque && $empaque->status ? 'checked' : '' }}
                                {{ $remission->status === 'ANULADO' ? 'disabled' : '' }}
                                class="w-5 h-5 rounded border-gray-300 text-green-600 focus:ring-green-500 {{ $remission->status === 'ANULADO' ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                            @if($empaque && $empaque->status)
                            <div class="text-[9px] text-gray-400 text-center leading-tight">
                                <p class="font-bold">{{ $empaque->user->name ?? 'Usuario' }}</p>
                                <p>{{ $empaque->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            @endif
                        </div>
                    </td>
                    
                    <td class="px-6 py-4">
                        <div class="flex flex-col items-center space-y-2">
                            <input type="checkbox" 
                                wire:click="toggleAuthorization({{ $remission->id }}, 'despacho')"
                                {{ $despacho && $despacho->status ? 'checked' : '' }}
                                {{ $remission->status === 'ANULADO' ? 'disabled' : '' }}
                                class="w-5 h-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500 {{ $remission->status === 'ANULADO' ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                            @if($despacho && $despacho->status)
                            <div class="text-[9px] text-gray-400 text-center leading-tight">
                                <p class="font-bold">{{ $despacho->user->name ?? 'Usuario' }}</p>
                                <p>{{ $despacho->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            @endif
                        </div>
                    </td>

                    <td class="px-6 py-4 text-center">
                        <div class="flex flex-col items-center space-y-2">
                            <input type="checkbox" 
                                wire:click="toggleAuthorization({{ $remission->id }}, 'pago')"
                                {{ $pago && $pago->status ? 'checked' : '' }}
                                {{ $remission->status === 'ANULADO' ? 'disabled' : '' }}
                                class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 {{ $remission->status === 'ANULADO' ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                            @if($pago)
                            <div class="text-[9px] text-center leading-tight {{ $pago->status ? 'text-gray-400' : 'text-red-500' }}">
                                <p class="font-bold">
                                    {{ $pago->status ? '' : 'Desconfirmado por: ' }}
                                    {{ $pago->user->name ?? 'Usuario' }}
                                </p>
                                <p>{{ $pago->created_at->format('Y-m-d H:i') }}</p>
                            </div>
                            @endif
                        </div>
                    </td>
                    
                    <td class="px-6 py-4 text-center">
                        <div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
                            <button @click="open = !open" class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                                <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>
                            
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-[60]"
                                 style="display: none;">
                                <div class="py-1">
                                    @if($remission->quote)
                                    <button wire:click="printQuote({{ $remission->quote->id }})" 
                                            @click="open = false"
                                            class="flex items-center w-full px-4 py-2 text-sm text-green-700 dark:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                        Imprimir
                                    </button>
                                    @endif
                                    <button wire:click="openObservationsModal({{ $remission->id }})" 
                                            @click="open = false"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors whitespace-nowrap">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        Observaciones
                                    </button>
                                    <button wire:click="openJustificacionModal({{ $remission->id }})" 
                                            @click="open = false"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors whitespace-nowrap">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Agregar obs cartera
                                    </button>
                                    <button wire:click="openFacturacionModal({{ $remission->id }})" 
                                            @click="open = false"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors whitespace-nowrap">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Agregar obs facturación
                                    </button>
                                    <button wire:click="openUploadModal({{ $remission->id }})" 
                                            @click="open = false"
                                            class="flex items-center w-full px-4 py-2 text-sm text-violet-700 dark:text-violet-300 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        {{ $remission->proof_payment ? 'Reemplazar soporte' : 'Cargar soporte' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="p-6">
            {{ $remissions->links() }}
        </div>
    </div>

    <!-- Componente de Observaciones -->
    <livewire:tenant.components.observations-modal />

    <!-- Modal de Carga de Soporte de Pago -->
    <template x-teleport="body">
        <div x-show="$wire.showUploadModal"
             class="fixed inset-0 z-[110] overflow-y-auto"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div class="flex items-center justify-center min-h-screen p-4">
                <!-- Overlay con blur -->
                <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm" @click="$wire.set('showUploadModal', false)"></div>

                <!-- Panel del modal -->
                <div class="relative z-10 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl border border-gray-100 dark:border-slate-700 overflow-hidden"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-4">

                    <!-- Header del modal -->
                    <div class="flex items-center justify-between px-6 py-4" style="background: linear-gradient(to right, #7c3aed, #4f46e5); border-bottom: 1px solid #e5e7eb;">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white">Gestionar Soportes de Pago</h3>
                                <p class="text-xs" style="color: #ddd6fe;">Remisión #{{ $selectedRemissionId }}</p>
                            </div>
                        </div>
                        <button @click="$wire.set('showUploadModal', false)" class="transition-colors" style="color: rgba(255,255,255,0.7);" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Cuerpo del modal -->
                    <div class="p-6">
                        @php
                            $currentRem = \App\Models\Tenant\Remissions\InvRemissions::find($selectedRemissionId);
                            $totalRemAmount = $currentRem ? floatval($currentRem->sub_total_rem + ($currentRem->flete ?? 0)) : 0;
                            $paymentTotal = $this->getPaymentRowsTotal();
                        @endphp

                        <div class="flex justify-between items-center mb-4 p-3 bg-gray-50 dark:bg-slate-900/60 rounded-xl border border-gray-100 dark:border-slate-700">
                            <div>
                                <span class="text-xs text-gray-500 block uppercase">Total del Pedido:</span>
                                <span class="text-lg font-black text-indigo-600 dark:text-indigo-400">${{ number_format($totalRemAmount, 0) }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-gray-500 block uppercase">Total Ingresado:</span>
                                <span class="text-lg font-black {{ $paymentTotal > $totalRemAmount ? 'text-red-500' : 'text-green-600' }}">
                                    ${{ number_format($paymentTotal, 0) }}
                                </span>
                            </div>
                                         <div class="space-y-4 max-h-[300px] overflow-y-auto pr-1">
                            @foreach($paymentRows as $index => $row)
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-3 bg-gray-50/50 dark:bg-slate-900/30 rounded-lg border border-gray-200 dark:border-slate-700 items-end" wire:key="cartera-payment-{{ $index }}">
                                    <!-- Método -->
                                    <div class="text-left md:col-span-4">
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Método de Pago</label>
                                        @php
                                            $selectedCarteraIds = collect($paymentRows)
                                                ->pluck('method_payment_id')
                                                ->filter()
                                                ->all();
                                        @endphp
                                        <select {{ $this->canEditPayments ? '' : 'disabled' }} wire:model.live="paymentRows.{{ $index }}.method_payment_id"
                                                class="w-full rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 dark:text-gray-300 text-xs focus:ring-indigo-500 {{ $this->canEditPayments ? '' : 'cursor-not-allowed bg-gray-100' }}">
                                            <option value="">Selecciona método</option>
                                            @foreach($methodPayments as $method)
                                                @if($method['id'] == $row['method_payment_id'] || !in_array($method['id'], $selectedCarteraIds))
                                                    <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Valor -->
                                    <div class="text-left md:col-span-4">
                                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Valor</label>
                                        <input type="number" {{ $this->canEditPayments ? '' : 'readonly' }} wire:model.blur="paymentRows.{{ $index }}.value"
                                               class="w-full rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 dark:text-gray-300 text-xs focus:ring-indigo-500 {{ $this->canEditPayments ? '' : 'cursor-not-allowed bg-gray-100' }}" placeholder="0">
                                    </div>

                                    <!-- Soporte -->
                                    <div class="text-left md:col-span-3">
                                        <div class="w-full">
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Soporte</label>
                                            @if(isset($paymentFiles[$index]))
                                                <div class="flex items-center justify-between p-1.5 bg-green-50 dark:bg-green-900/20 border border-green-200 rounded-lg text-[10px]">
                                                    <span class="text-green-800 dark:text-green-300 truncate max-w-[120px] font-medium">
                                                        {{ $paymentFiles[$index]->getClientOriginalName() }}
                                                    </span>
                                                    <button type="button" wire:click="$set('paymentFiles.{{ $index }}', null)" class="text-red-500 hover:text-red-700">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            @elseif(!empty($row['proof_payment']))
                                                <div class="flex items-center justify-between p-1.5 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 rounded-lg text-[10px]">
                                                    <a href="{{ asset('storage/' . $row['proof_payment']) }}" target="_blank" class="text-indigo-700 dark:text-indigo-300 font-bold hover:underline truncate max-w-[120px]">
                                                        Ver Soporte
                                                    </a>
                                                    <input type="file" wire:model="paymentFiles.{{ $index }}" accept="image/*,application/pdf" class="hidden" id="file-input-{{ $index }}">
                                                    <button type="button" onclick="document.getElementById('file-input-{{ $index }}').click()" class="text-indigo-500 hover:text-indigo-700" title="Reemplazar soporte">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                    </button>
                                                </div>
                                            @else
                                                <input type="file" wire:model="paymentFiles.{{ $index }}" accept="image/*,application/pdf"
                                                       class="w-full text-[10px] text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 hover:file:bg-indigo-100 cursor-pointer">
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Eliminar fila (Solo Caleb o Yaneth si hay más de 1 fila) -->
                                    <div class="md:col-span-1 flex justify-center pb-1">
                                        @if($this->canEditPayments && count($paymentRows) > 1)
                                            <button type="button" wire:click="removePaymentRow({{ $index }})" class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Botón Agregar fila (Solo Caleb o Yaneth) -->
                        @if($this->canEditPayments)
                            <div class="mt-3 flex justify-start">
                                <button type="button" wire:click="addPaymentRow" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 flex items-center gap-1 bg-indigo-50 dark:bg-indigo-950/20 px-3 py-1.5 rounded-lg border border-indigo-200 dark:border-indigo-900 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Agregar Forma de Pago
                                </button>
                            </div>
                        @endif

                        <!-- Campo de Justificación (Solo Caleb o Yaneth) -->
                        @if($this->canEditPayments)
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-slate-700">
                                <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase mb-2">Justificación del Cambio (Requerida si modifica formas de pago)</label>
                                <textarea wire:model="editJustificacionText" rows="3"
                                          class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-xs focus:ring-indigo-500 focus:border-indigo-500"
                                          placeholder="Escriba el motivo detallado de la modificación de las formas de pago..."></textarea>
                            </div>
                        @endif
                    </div>              </div>

                    <!-- Footer con botones -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/40 border-t border-gray-100 dark:border-slate-700 flex justify-end gap-3">
                        <button
                            @click="$wire.set('showUploadModal', false)"
                            class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-slate-300 bg-white dark:bg-slate-700 border border-gray-300 dark:border-slate-600 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                            Cancelar
                        </button>
                        <button
                            @click="$wire.call('saveProofPayment')"
                            wire:loading.attr="disabled"
                            wire:target="saveProofPayment"
                            class="px-5 py-2 text-sm font-bold text-white rounded-lg transition-all duration-200 flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed"
                            style="background: linear-gradient(to right, #7c3aed, #4f46e5); box-shadow: 0 4px 14px 0 rgba(124, 58, 237, 0.4);"
                            onmouseover="this.style.background='linear-gradient(to right, #6d28d9, #4338ca)'"
                            onmouseout="this.style.background='linear-gradient(to right, #7c3aed, #4f46e5)'">
                            <svg wire:loading.remove wire:target="saveProofPayment" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <svg wire:loading wire:target="saveProofPayment" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            <span wire:loading.remove wire:target="saveProofPayment">Guardar Pagos</span>
                            <span wire:loading wire:target="saveProofPayment">Guardando...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal de Justificación de Cartera -->
    <template x-teleport="body">
        <div x-show="$wire.showJustificacionModal" 
             class="fixed inset-0 z-[100] overflow-y-auto" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-slate-900/80 transition-opacity" @click="$wire.showJustificacionModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-middle bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-slate-700">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 text-center">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                            {{ $isDesconfirmarPago ? 'Justificación requerida' : 'Agregar observación de cartera' }}
                        </h3>
                    </div>

                    <div class="p-6">
                        <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-4 text-center uppercase tracking-wide">
                            {{ $isDesconfirmarPago ? 'Por favor, justifique por qué está desconfirmando el pago:' : 'Observación de Cartera' }}
                        </label>
                        <textarea wire:model="justificacionText" 
                                  rows="4" 
                                  class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="{{ $isDesconfirmarPago ? 'Escribe tu justificación aquí...' : 'Escribe aquí tu comentario...' }}"></textarea>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/50 flex justify-center space-x-3">
                        <button wire:click="saveJustificacion" 
                                class="px-8 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition-colors shadow-lg shadow-blue-600/20">
                            {{ $isDesconfirmarPago ? 'Enviar' : 'Guardar' }}
                        </button>
                        <button @click="$wire.showJustificacionModal = false" 
                                class="px-8 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-bold transition-colors shadow-lg shadow-slate-600/20">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Modal de Observación de Facturación (desde Cartera) -->
    <template x-teleport="body">
        <div x-show="$wire.showFacturacionModal" 
             class="fixed inset-0 z-[100] overflow-y-auto" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500/75 dark:bg-slate-900/80 transition-opacity" @click="$wire.showFacturacionModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-middle bg-white dark:bg-slate-800 rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-200 dark:border-slate-700">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 text-center">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                            Agregar observación
                        </h3>
                    </div>

                    <div class="p-6">
                        <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-4 text-center uppercase tracking-wide">
                            Observación de Facturación
                        </label>
                        <textarea wire:model="facturacionText" 
                                  rows="4" 
                                  class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Escribe aquí tu comentario..."></textarea>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/50 flex justify-center space-x-3">
                        <button wire:click="saveFacturacion" 
                                class="px-8 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition-colors shadow-lg shadow-blue-600/20">
                            Guardar
                        </button>
                        <button @click="$wire.showFacturacionModal = false" 
                                class="px-8 py-2 bg-slate-600 hover:bg-slate-700 text-white rounded-lg font-bold transition-colors shadow-lg shadow-slate-600/20">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
