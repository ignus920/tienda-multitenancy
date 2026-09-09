<div class="min-h-screen bg-gray-50 px-4 pb-16 pt-4 dark:bg-gray-950 sm:px-6 print:bg-white">
    <div class="print:hidden"><x-portal-nav active="orders" /></div>

    <div class="mx-auto max-w-4xl">

        {{-- Volver --}}
        <a href="{{ route('tenant.client.orders') }}" wire:navigate
           class="mb-4 inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white print:hidden">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Mis Pedidos
        </a>

        {{-- Encabezado --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-extrabold text-gray-900 dark:text-white sm:text-2xl">Pedido #{{ $remission->consecutive }}</h1>
                        <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $badge['color'] }}">{{ $badge['label'] }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Realizado el {{ \Carbon\Carbon::parse($remission->created_at)->translatedFormat('d \d\e F Y, g:i a') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 print:hidden">
                    <button type="button" onclick="window.print()"
                            class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-bold text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Imprimir
                    </button>
                    @if ($invoice && $invoice->api_data_id)
                        <a href="{{ route('tenant.client.invoice.pdf', $invoice->id) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-bold text-white transition hover:bg-indigo-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Factura PDF
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-5">

            {{-- Seguimiento --}}
            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900 sm:p-6">
                    <h2 class="mb-5 text-sm font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500">Seguimiento del pedido</h2>

                    @php $isCancelled = in_array(strtoupper($remission->status), ['ANULADO','DEVUELTO','VENCIDO']); @endphp

                    @if ($isCancelled)
                        <div class="mb-5 flex items-center gap-3 rounded-xl bg-red-50 p-3.5 text-sm font-semibold text-red-700 dark:bg-red-900/20 dark:text-red-300">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Este pedido está {{ strtolower($badge['label']) }}.
                        </div>
                    @endif

                    <ol class="relative">
                        @foreach ($timeline as $i => $step)
                            <li class="relative flex gap-4 pb-7 last:pb-0">
                                {{-- línea vertical --}}
                                @if (! $loop->last)
                                    <span @class([
                                        'absolute left-[15px] top-8 h-full w-0.5 -translate-x-1/2',
                                        'bg-indigo-500 dark:bg-indigo-400' => $step['state'] === 'done',
                                        'bg-gray-200 dark:bg-gray-700' => $step['state'] !== 'done',
                                    ])></span>
                                @endif
                                {{-- punto --}}
                                <span @class([
                                    'relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2',
                                    'border-indigo-500 bg-indigo-500 text-white dark:border-indigo-400 dark:bg-indigo-400' => $step['state'] === 'done',
                                    'border-indigo-500 bg-white text-indigo-600 ring-4 ring-indigo-100 dark:border-indigo-400 dark:bg-gray-900 dark:text-indigo-300 dark:ring-indigo-900/40' => $step['state'] === 'current',
                                    'border-gray-300 bg-white text-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-600' => $step['state'] === 'pending',
                                ])>
                                    @if ($step['state'] === 'done')
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    @elseif ($step['state'] === 'current')
                                        <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-indigo-500 dark:bg-indigo-400"></span>
                                    @else
                                        <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
                                    @endif
                                </span>
                                {{-- texto --}}
                                <div class="pt-1">
                                    <p @class([
                                        'text-sm font-bold',
                                        'text-gray-900 dark:text-white' => $step['state'] !== 'pending',
                                        'text-gray-400 dark:text-gray-600' => $step['state'] === 'pending',
                                    ])>{{ $step['label'] }}</p>
                                    @if ($step['date'])
                                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                            {{ \Carbon\Carbon::parse($step['date'])->translatedFormat('d M Y · g:i a') }}
                                        </p>
                                    @elseif ($step['state'] === 'current')
                                        <p class="mt-0.5 text-xs font-semibold text-indigo-500 dark:text-indigo-400">En proceso</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>

                {{-- Productos --}}
                <div class="mt-4 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="border-b border-gray-100 px-5 py-4 text-sm font-bold uppercase tracking-wide text-gray-400 dark:border-gray-800 dark:text-gray-500">
                        Productos ({{ $lines->count() }})
                    </h2>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($lines as $line)
                            <li class="flex items-start gap-3 px-5 py-3.5">
                                <span class="mt-0.5 flex h-7 min-w-[2rem] items-center justify-center rounded-lg bg-gray-100 px-1.5 text-xs font-extrabold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    {{ rtrim(rtrim(number_format($line['quantity'], 2, '.', ''), '0'), '.') }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $line['description'] }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">${{ number_format($line['unit'], 0, ',', '.') }} c/u</p>
                                </div>
                                <p class="whitespace-nowrap text-sm font-extrabold text-gray-900 dark:text-white">${{ number_format($line['subtotal'], 0, ',', '.') }}</p>
                            </li>
                        @endforeach
                    </ul>
                    <div class="space-y-1.5 border-t border-gray-100 px-5 py-4 text-sm dark:border-gray-800">
                        <div class="flex justify-between text-gray-500 dark:text-gray-400">
                            <span>Productos</span><span>${{ number_format($productsTotal, 0, ',', '.') }}</span>
                        </div>
                        @if ($flete > 0)
                            <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                <span>Envío</span><span>${{ number_format($flete, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-100 pt-2 text-base font-extrabold text-gray-900 dark:border-gray-800 dark:text-white">
                            <span>Total</span><span>${{ number_format($grandTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lateral: entrega + factura --}}
            <div class="space-y-4 lg:col-span-2">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500">Entrega</h2>
                    <dl class="space-y-3 text-sm">
                        @if ($remission->deliveryDate)
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 dark:text-gray-500">Fecha estimada</dt>
                                <dd class="font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($remission->deliveryDate)->translatedFormat('d \d\e F Y') }}</dd>
                            </div>
                        @endif
                        @if ($deliveryTypeName)
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 dark:text-gray-500">Tipo de entrega</dt>
                                <dd class="font-semibold text-gray-800 dark:text-gray-200">{{ $deliveryTypeName }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-semibold text-gray-400 dark:text-gray-500">Dirección</dt>
                            <dd class="flex items-start gap-1.5 font-semibold text-gray-800 dark:text-gray-200">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $remission->observations_delivery ?: 'No especificada' }}</span>
                            </dd>
                        </div>
                        @if ($remission->obs)
                            <div>
                                <dt class="text-xs font-semibold text-gray-400 dark:text-gray-500">Observaciones</dt>
                                <dd class="text-gray-600 dark:text-gray-300">{{ $remission->obs }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-400 dark:text-gray-500">Factura</h2>
                    @if ($invoice)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-extrabold text-gray-900 dark:text-white">
                                    {{ $invoice->invoiceNumber ?: ('N° ' . $invoice->consecutive) }}
                                </p>
                                <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-[11px] font-bold {{ $invoiceBadge['color'] }}">{{ $invoiceBadge['label'] }}</span>
                            </div>
                            @if ($invoice->api_data_id)
                                <a href="{{ route('tenant.client.invoice.pdf', $invoice->id) }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-indigo-700">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    PDF
                                </a>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-400 dark:text-gray-500">
                            Este pedido todavía no tiene factura emitida.
                        </p>
                    @endif
                </div>

                @if ($remission->proof_payment)
                    <div class="flex items-center gap-2 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-700 dark:border-green-900 dark:bg-green-900/20 dark:text-green-300">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Comprobante de pago recibido
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
