<div class="fp">
    <x-portal-nav active="invoices" />

    <div class="fp-wrap">
        <div class="fp-greet">
            <div>
                <div class="fp-eyebrow">Documentos</div>
                <h1 class="fp-h1">Mis Facturas</h1>
                <p>Descargá el PDF oficial de tus facturas electrónicas.</p>
            </div>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:18px">
            <div class="fp-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-4.3-4.3M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar por número de factura…">
            </div>
            <div style="display:flex; gap:8px">
                @foreach (['' => 'Todas', 'pending' => 'Pendientes', 'paid' => 'Pagadas'] as $val => $lbl)
                    <button type="button" wire:click="$set('paymentFilter', '{{ $val }}')"
                            class="fp-fpill" @if($paymentFilter === $val) aria-pressed="true" @endif>{{ $lbl }}</button>
                @endforeach
            </div>
        </div>

        <div class="fp-panel" wire:loading.class="opacity-50">
            @if ($rows->isEmpty())
                <div class="fp-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <p>@if ($search !== '' || $paymentFilter !== '') No hay facturas con esos filtros @else Todavía no tienes facturas @endif</p>
                    @if ($search !== '' || $paymentFilter !== '')
                        <button wire:click="clearFilters" class="fp-btn ghost sm" style="margin-top:12px">Quitar filtros</button>
                    @endif
                </div>
            @else
                <div class="fp-tablewrap">
                    <table class="fp-inv">
                        <thead><tr>
                            <th>Factura</th><th>Fecha</th><th>Pedido</th><th class="r">Total</th><th>Estado</th><th class="r">PDF</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($rows as $inv)
                                <tr wire:key="inv-{{ $inv['id'] }}">
                                    <td><span class="fnum">{{ $inv['number'] }}</span></td>
                                    <td style="color:var(--fp-ink-2)">{{ \Carbon\Carbon::parse($inv['date'])->translatedFormat('d M Y') }}</td>
                                    <td>
                                        @if ($inv['order_id'])
                                            <a href="{{ route('tenant.client.orders.show', $inv['order_id']) }}" wire:navigate style="color:var(--fp-accent); font-weight:700">#{{ $inv['order_no'] }}</a>
                                        @else
                                            <span style="color:var(--fp-ink-3)">—</span>
                                        @endif
                                    </td>
                                    <td class="r"><span class="amt">${{ number_format($inv['total'], 0, ',', '.') }}</span></td>
                                    <td><span class="fp-pill {{ $inv['badge']['fp'] }}"><span class="dot"></span>{{ $inv['badge']['label'] }}</span></td>
                                    <td class="r">
                                        @if ($inv['downloadable'])
                                            <a href="{{ route('tenant.client.invoice.pdf', $inv['id']) }}" target="_blank" rel="noopener" class="fp-btn sm">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 10v7m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                Descargar
                                            </a>
                                        @else
                                            <span style="font-size:11px; font-weight:600; color:var(--fp-ink-3)">Sin emitir</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($invoices->hasPages())
            <div class="fp-pagination">{{ $invoices->links() }}</div>
        @endif
    </div>
</div>
