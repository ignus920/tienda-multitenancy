<div class="fp">
    <x-portal-nav active="quotes" />

    <div class="fp-wrap">
        <div class="fp-greet">
            <div>
                <div class="fp-eyebrow">Seguimiento</div>
                <h1 class="fp-h1">Mis Cotizaciones</h1>
                <p>Pedidos que enviaste desde el catálogo, antes de convertirse en OP.</p>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:12px" wire:loading.class="opacity-50">
            @forelse ($rows as $q)
                <div class="fp-ocard" wire:key="quote-{{ $q['id'] }}">
                    <div style="display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:12px">
                        <div style="display:flex; align-items:center; gap:13px">
                            <span class="fp-obox" style="width:46px;height:46px"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 12h6m-6 4h6M8 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6H8Z" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                            <div>
                                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap">
                                    <b style="font-family:Archivo; font-weight:800; font-size:15px">Cotización&nbsp;#{{ $q['consecutive'] }}</b>
                                    @if ($q['has_order'])
                                        <span class="fp-pill green"><span class="dot"></span>OP generada · #{{ $q['order_consecutive'] }}</span>
                                    @elseif ($q['confirmed_at'])
                                        <span class="fp-pill blue"><span class="dot"></span>Confirmado por ti</span>
                                    @elseif ($q['client_note'])
                                        <span class="fp-pill amber"><span class="dot"></span>Ventas confirmó cantidades</span>
                                    @else
                                        <span class="fp-pill gray"><span class="dot"></span>Enviada, en revisión</span>
                                    @endif
                                </div>
                                <p style="font-size:12px; color:var(--fp-ink-3); margin-top:2px">
                                    {{ \Carbon\Carbon::parse($q['date'])->translatedFormat('d \d\e F Y') }}
                                    · {{ $q['items'] }} {{ \Illuminate\Support\Str::plural('producto', $q['items']) }}
                                </p>
                            </div>
                        </div>
                        <div style="text-align:right">
                            <p style="font-family:Archivo; font-weight:800; font-size:17px">${{ number_format($q['total'], 0, ',', '.') }}</p>
                        </div>
                    </div>

                    @if ($q['client_note'])
                        <div class="oc-foot" style="flex-direction:column; align-items:flex-start; gap:8px">
                            <span style="display:inline-flex; align-items:flex-start; gap:6px; color:var(--fp-ink-2)">
                                <svg style="flex:none;margin-top:2px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M8 12h8M8 16h5M8 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6H8Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span><b style="color:var(--fp-ink)">Nota de ventas:</b> {{ $q['client_note'] }}</span>
                            </span>

                            @if (!$q['confirmed_at'] && !$q['has_order'])
                                <button type="button" wire:click="confirmOrder({{ $q['id'] }})"
                                    wire:loading.attr="disabled" wire:target="confirmOrder({{ $q['id'] }})"
                                    class="fp-btn sm">
                                    Confirmar Pedido
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="fp-panel fp-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m-6 4h6M8 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6H8Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <p>Todavía no has enviado cotizaciones desde el catálogo</p>
                </div>
            @endforelse
        </div>

        @if ($quotes->hasPages())
            <div class="fp-pagination">{{ $quotes->links() }}</div>
        @endif
    </div>
</div>
