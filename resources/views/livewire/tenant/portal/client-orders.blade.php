<div class="fp">
    <x-portal-nav active="orders" />

    <div class="fp-wrap">
        <div class="fp-greet">
            <div>
                <div class="fp-eyebrow">Seguimiento</div>
                <h1 class="fp-h1">Mis Pedidos</h1>
                <p>Estado y detalle de todos los pedidos de tu empresa.</p>
            </div>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:18px">
            <div class="fp-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 21-4.3-4.3M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar por número de pedido…">
            </div>
            <div style="display:flex; gap:8px; overflow-x:auto">
                @foreach (['' => 'Todos', 'in_progress' => 'En curso', 'delivered' => 'Entregados', 'cancelled' => 'Anulados'] as $val => $lbl)
                    <button type="button" wire:click="$set('statusFilter', '{{ $val }}')"
                            class="fp-fpill" @if($statusFilter === $val) aria-pressed="true" @endif>{{ $lbl }}</button>
                @endforeach
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:12px" wire:loading.class="opacity-50">
            @forelse ($rows as $o)
                <a href="{{ route('tenant.client.orders.show', $o['id']) }}" wire:navigate wire:key="order-{{ $o['id'] }}" class="fp-ocard">
                    <div style="display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:12px">
                        <div style="display:flex; align-items:center; gap:13px">
                            <span class="fp-obox" style="width:46px;height:46px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 8h14M5 8a2 2 0 1 1 0-4h14a2 2 0 1 1 0 4M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8m-9 4h4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            <div>
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap">
                                    <b style="font-family:Archivo; font-weight:800; font-size:15px">Pedido&nbsp;#{{ $o['consecutive'] }}</b>
                                    <span class="fp-pill {{ $o['badge']['fp'] }}"><span class="dot"></span>{{ $o['badge']['label'] }}</span>
                                </div>
                                <p style="font-size:12px; color:var(--fp-ink-3); margin-top:2px">
                                    {{ \Carbon\Carbon::parse($o['date'])->translatedFormat('d \d\e F Y') }}
                                    · {{ $o['items'] }} {{ \Illuminate\Support\Str::plural('producto', $o['items']) }}
                                </p>
                            </div>
                        </div>
                        <div style="text-align:right">
                            <p style="font-family:Archivo; font-weight:800; font-size:17px">${{ number_format($o['total'], 0, ',', '.') }}</p>
                            @if ($o['invoice_no'])<p style="font-size:11px; font-weight:600; color:var(--fp-ink-3)">Factura {{ $o['invoice_no'] }}</p>@endif
                        </div>
                    </div>
                    <div class="oc-foot">
                        @if ($o['delivery'])
                            <span style="display:inline-flex; align-items:center; gap:6px">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Entrega <b style="color:var(--fp-ink)">{{ \Carbon\Carbon::parse($o['delivery'])->translatedFormat('d M Y') }}</b>
                            </span>
                        @endif
                        @if ($o['address'])
                            <span style="display:inline-flex; align-items:center; gap:6px; min-width:0; max-width:100%">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex:none"><path d="M17.657 16.657L13.414 20.9a2 2 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z" stroke-linecap="round" stroke-linejoin="round"/><path d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap">{{ $o['address'] }}</span>
                            </span>
                        @endif
                        <span style="margin-left:auto; display:inline-flex; align-items:center; gap:5px; font-weight:700; color:var(--fp-accent)">
                            Ver seguimiento
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px"><path d="m9 5 7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </div>
                </a>
            @empty
                <div class="fp-panel fp-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <p>@if ($search !== '' || $statusFilter !== '') No hay pedidos con esos filtros @else Todavía no tienes pedidos @endif</p>
                    @if ($search !== '' || $statusFilter !== '')
                        <button wire:click="clearFilters" class="fp-btn ghost sm" style="margin-top:12px">Quitar filtros</button>
                    @endif
                </div>
            @endforelse
        </div>

        @if ($orders->hasPages())
            <div class="fp-pagination">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
