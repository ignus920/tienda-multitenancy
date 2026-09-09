<div class="fp">
    <div class="print:hidden"><x-portal-nav active="orders" /></div>

    <div class="fp-wrap">
        <a href="{{ route('tenant.client.orders') }}" wire:navigate class="fp-back print:hidden">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Volver a Mis Pedidos
        </a>

        <div class="fp-panel fp-odhead">
            <div>
                <h1>Pedido&nbsp;#{{ $remission->consecutive }} <span class="fp-pill {{ $badge['fp'] }}"><span class="dot"></span>{{ $badge['label'] }}</span></h1>
                <div class="when">Realizado el {{ \Carbon\Carbon::parse($remission->created_at)->translatedFormat('d \d\e F Y, g:i a') }}</div>
            </div>
            <div class="fp-odhead-actions print:hidden" style="display:flex; gap:8px; flex-wrap:wrap">
                <button type="button" onclick="window.print()" class="fp-btn ghost sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V4h12v5M6 18H4v-4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4h-2M8 14h8v6H8Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Imprimir
                </button>
                @if ($invoice && $invoice->api_data_id && $invoice->status === 'FACTURADO')
                    <a href="{{ route('tenant.client.invoice.pdf', $invoice->id) }}" target="_blank" rel="noopener" class="fp-btn sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 10v7m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Factura PDF
                    </a>
                @endif
            </div>
        </div>

        @php $isCancelled = in_array(strtoupper($remission->status), ['ANULADO','DEVUELTO','VENCIDO'], true); @endphp

        <div class="fp-odgrid">
            <div class="fp-col">
                <div class="fp-panel fp-pad">
                    <h2 class="fp-section" style="margin-bottom:18px">Seguimiento</h2>

                    @if ($isCancelled)
                        <div style="display:flex; align-items:center; gap:11px; padding:12px 14px; border-radius:11px; background:var(--fp-bad-soft); color:var(--fp-bad); font-size:13px; font-weight:600; margin-bottom:18px">
                            <svg style="width:18px;height:18px;flex:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Este pedido está {{ strtolower($badge['label']) }}.
                        </div>
                    @endif

                    <ol class="fp-timeline">
                        @foreach ($timeline as $step)
                            @php $st = $step['state'] === 'current' ? 'now' : $step['state']; @endphp
                            <li class="{{ $st }}">
                                @if (! $loop->last)<span class="line"></span>@endif
                                <span class="knob">
                                    @if ($st === 'done')
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    @else
                                        <span class="pt"></span>
                                    @endif
                                </span>
                                <div class="txt">
                                    <b>{{ $step['label'] }}</b>
                                    @if ($step['date'])
                                        <time>{{ \Carbon\Carbon::parse($step['date'])->translatedFormat('d M Y · g:i a') }}</time>
                                    @elseif ($st === 'now')
                                        <time>En proceso</time>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="fp-panel">
                    <h2 class="fp-section" style="padding:16px 20px 0; margin-bottom:12px">
                        Productos <span style="font-family:'Public Sans'; font-weight:600; font-size:12px; color:var(--fp-ink-3)">{{ $lines->count() }} {{ \Illuminate\Support\Str::plural('ítem', $lines->count()) }}</span>
                    </h2>
                    <ul class="fp-lines">
                        @foreach ($lines as $line)
                            <li>
                                <span class="fp-qty">{{ rtrim(rtrim(number_format($line['quantity'], 2, '.', ''), '0'), '.') }}</span>
                                <div class="li-body">
                                    <b>{{ $line['description'] }}</b>
                                    <div class="u">${{ number_format($line['unit'], 0, ',', '.') }} c/u</div>
                                </div>
                                <span class="li-amt">${{ number_format($line['subtotal'], 0, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="fp-totals">
                        <div class="tr"><span>Productos</span><span class="mono">${{ number_format($productsTotal, 0, ',', '.') }}</span></div>
                        @if ($flete > 0)
                            <div class="tr"><span>Envío</span><span class="mono">${{ number_format($flete, 0, ',', '.') }}</span></div>
                        @endif
                        <div class="tr grand"><span>Total</span><span class="mono">${{ number_format($grandTotal, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>

            <div class="fp-col">
                <div class="fp-panel fp-pad">
                    <h2 class="fp-section" style="margin-bottom:14px">Entrega</h2>
                    <dl class="fp-kv">
                        @if ($remission->deliveryDate)
                            <div><dt>Fecha estimada</dt><dd>{{ \Carbon\Carbon::parse($remission->deliveryDate)->translatedFormat('l d \d\e F Y') }}</dd></div>
                        @endif
                        @if ($deliveryTypeName)
                            <div><dt>Tipo de entrega</dt><dd>{{ $deliveryTypeName }}</dd></div>
                        @endif
                        <div><dt>Dirección</dt><dd>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M17.657 16.657L13.414 20.9a2 2 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>{{ $remission->observations_delivery ?: 'No especificada' }}</span>
                        </dd></div>
                        @if ($remission->obs)
                            <div><dt>Observaciones</dt><dd style="font-weight:400; color:var(--fp-ink-2)">{{ $remission->obs }}</dd></div>
                        @endif
                    </dl>
                </div>

                <div class="fp-panel fp-pad">
                    <h2 class="fp-section" style="margin-bottom:12px">Factura</h2>
                    @if ($invoice)
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px">
                            <div>
                                <div class="mono" style="font-weight:600; font-size:15px">{{ $invoice->invoiceNumber ?: ('N° ' . $invoice->consecutive) }}</div>
                                <span class="fp-pill {{ $invoiceBadge['fp'] }}" style="margin-top:6px"><span class="dot"></span>{{ $invoiceBadge['label'] }}</span>
                            </div>
                            @if ($invoice->api_data_id && $invoice->status === 'FACTURADO')
                                <a href="{{ route('tenant.client.invoice.pdf', $invoice->id) }}" target="_blank" rel="noopener" class="fp-btn sm">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 10v7m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    PDF
                                </a>
                            @endif
                        </div>
                    @else
                        <p style="font-size:13px; color:var(--fp-ink-3)">Este pedido todavía no tiene factura emitida.</p>
                    @endif
                </div>

                @if ($remission->proof_payment)
                    <div style="display:flex; align-items:center; gap:9px; padding:13px 15px; border-radius:12px; background:var(--fp-good-soft); color:var(--fp-good); font-size:12.5px; font-weight:600">
                        <svg style="width:18px;height:18px;flex:none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Comprobante de pago recibido
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
