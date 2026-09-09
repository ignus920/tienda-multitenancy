<div class="fp">
    <x-portal-nav active="dashboard" />

    <div class="fp-wrap">
        <div class="fp-greet">
            <div>
                <div class="fp-eyebrow">{{ \Carbon\Carbon::now()->translatedFormat('l d \d\e F') }}</div>
                <h1 class="fp-h1">Hola, {{ $companyName }}</h1>
                <p>Este es el resumen de tu cuenta.</p>
            </div>
            <a href="{{ route('tenant.client.portal') }}" wire:navigate class="fp-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 6h15l-1.5 9h-12z M6 6 5 3H2M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Hacer un pedido
            </a>
        </div>

        {{-- Cinta: próxima entrega --}}
        @php
            $ribbonSteps = ['Recibido', 'Alistamiento', 'Empacado', 'En camino', 'Entregado'];
            $ribbonTimeline = $nextDelivery ? $this->buildOrderTimeline($nextDelivery) : [];
            $ribbonReached = -1;
            foreach ($ribbonTimeline as $i => $s) { if ($s['state'] !== 'pending') $ribbonReached = $i; }
        @endphp
        @if ($nextDelivery)
            <div class="fp-ribbon">
                <span class="beam" aria-hidden="true"></span>
                <div class="r-eyebrow">Tu próxima entrega</div>
                <div class="r-main">
                    <span class="big">
                        @if ($nextDelivery->deliveryDate)
                            {{ \Carbon\Carbon::parse($nextDelivery->deliveryDate)->translatedFormat('l d \d\e F') }}
                        @else
                            Fecha por confirmar
                        @endif
                    </span>
                    <span class="ord">Pedido&nbsp;#{{ $nextDelivery->consecutive }}</span>
                </div>
                <div class="track" aria-hidden="true">
                    @foreach ($ribbonSteps as $i => $lbl)
                        <span class="node {{ $i < $ribbonReached ? 'done' : ($i === $ribbonReached ? 'now' : '') }}"></span>
                        @if (! $loop->last)
                            <span class="seg {{ $i < $ribbonReached ? 'done' : '' }}"></span>
                        @endif
                    @endforeach
                </div>
                <div class="track-labels">
                    @foreach ($ribbonSteps as $i => $lbl)
                        <span>@if($i === $ribbonReached)<b>{{ $lbl }}</b>@else{{ $lbl }}@endif</span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Tiles --}}
        <div class="fp-stats">
            <a class="fp-tile" href="{{ route('tenant.client.orders') }}" wire:navigate>
                <div class="t-head"><span class="t-ic ic-glow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 17a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm11 0a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z M13 16V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v10h2m8 0H9m4 0a1 1 0 0 0 1 1M13 9h4l3 3v4h-2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>En curso</div>
                <div class="num">{{ $ordersInProgress }}</div>
                <div class="sub">{{ \Illuminate\Support\Str::plural('pedido', $ordersInProgress) }} {{ \Illuminate\Support\Str::plural('activo', $ordersInProgress) }}</div>
            </a>
            <a class="fp-tile {{ $unpaidCount > 0 ? 'attn' : '' }}" href="{{ route('tenant.client.invoices') }}" wire:navigate>
                <div class="t-head"><span class="t-ic ic-warm"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z M9 13h6M9 17h4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Por pagar</div>
                <div class="num">{{ $unpaidCount }}</div>
                <div class="sub">
                    @if ($unpaidTotal > 0) ${{ number_format($unpaidTotal, 0, ',', '.') }} pendiente @else facturas @endif
                </div>
            </a>
            <div class="fp-tile">
                <div class="t-head"><span class="t-ic ic-mut"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Historial</div>
                <div class="num">{{ $totalOrders }}</div>
                <div class="sub">{{ \Illuminate\Support\Str::plural('pedido', $totalOrders) }} en total</div>
            </div>
        </div>

        {{-- Últimos pedidos + rail --}}
        <div class="fp-grid2">
            <div>
                <h2 class="fp-section">Últimos pedidos <a href="{{ route('tenant.client.orders') }}" wire:navigate>Ver todos</a></h2>
                @if ($recentOrders->isEmpty())
                    <div class="fp-panel fp-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <p>Todavía no tienes pedidos</p>
                        <span>Cuando hagas tu primer pedido, aparecerá acá.</span>
                    </div>
                @else
                    <div class="fp-panel">
                        @foreach ($recentOrders as $o)
                            <a class="fp-row" href="{{ route('tenant.client.orders.show', $o['id']) }}" wire:navigate wire:key="recent-{{ $o['id'] }}">
                                <span class="fp-obox"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M5 8h14M5 8a2 2 0 1 1 0-4h14a2 2 0 1 1 0 4M5 8v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8m-9 4h4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                                <div class="o-body">
                                    <div class="o-t"><b>Pedido&nbsp;#{{ $o['consecutive'] }}</b><span class="fp-pill {{ $o['badge']['fp'] }}"><span class="dot"></span>{{ $o['badge']['label'] }}</span></div>
                                    <div class="o-meta">
                                        {{ \Carbon\Carbon::parse($o['date'])->translatedFormat('d M Y') }}
                                        · {{ $o['items'] }} {{ \Illuminate\Support\Str::plural('producto', $o['items']) }}
                                        @if ($o['invoice_no']) · Factura {{ $o['invoice_no'] }} @endif
                                    </div>
                                </div>
                                <div class="o-amt">
                                    <b>${{ number_format($o['total'], 0, ',', '.') }}</b>
                                    @if ($o['delivery'])<span>Entrega {{ \Carbon\Carbon::parse($o['delivery'])->translatedFormat('d M') }}</span>@endif
                                </div>
                                <svg class="fp-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="m9 5 7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <h2 class="fp-section">Accesos rápidos</h2>
                <div class="fp-panel">
                    <a class="fp-qa" href="{{ route('tenant.client.portal') }}" wire:navigate>
                        <span class="qa-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M6 6h15l-1.5 9h-12z M6 6 5 3H2M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <span><b>Hacer un pedido nuevo</b><span>Explorá el catálogo</span></span>
                    </a>
                    <a class="fp-qa" href="{{ route('tenant.client.orders') }}" wire:navigate>
                        <span class="qa-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M16 11V7a4 4 0 0 0-8 0v4M5 9h14l1 11H4Z" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <span><b>Ver todos mis pedidos</b><span>Seguimiento y detalle</span></span>
                    </a>
                    <a class="fp-qa" href="{{ route('tenant.client.invoices') }}" wire:navigate>
                        <span class="qa-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 3v12m0 0-4-4m4 4 4-4M5 21h14" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <span><b>Descargar facturas</b><span>PDF oficial electrónico</span></span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
