<?php

namespace App\Livewire\Tenant\Portal;

use App\Traits\InteractsWithClientPortal;
use Livewire\Component;
use Livewire\WithPagination;

class ClientQuotes extends Component
{
    use InteractsWithClientPortal;
    use WithPagination;

    public int $perPage = 12;

    public function boot(): void
    {
        $this->ensureTenantConnection();
    }

    /**
     * El cliente da su visto bueno final a una cotización ya revisada por
     * ventas (con nota de cantidades confirmadas). El asesor comercial
     * queda libre de convertirla en OP.
     */
    public function confirmOrder(int $quoteId): void
    {
        $quote = $this->findClientQuoteOrFail($quoteId);

        if (empty($quote->client_note)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Todavía no hay una confirmación de ventas para este pedido.',
            ]);
            return;
        }

        $quote->update(['client_confirmed_at' => now()]);

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Confirmaste tu pedido. Nuestro equipo comercial generará tu OP.',
        ]);
    }

    public function render()
    {
        $quotes = $this->clientQuoteQuery()
            ->with(['detalles', 'remissions'])
            ->latest('id')
            ->paginate($this->perPage);

        $rows = collect($quotes->items())->map(function ($q) {
            $hasOrder = $q->remissions->isNotEmpty();

            return [
                'id'          => $q->id,
                'consecutive' => $q->consecutive,
                'date'        => $q->created_at,
                'items'       => $q->detalles->count(),
                'total'       => (float) $q->total,
                'client_note' => $q->client_note,
                'confirmed_at'=> $q->client_confirmed_at,
                'has_order'   => $hasOrder,
                'order_consecutive' => $hasOrder ? $q->remissions->first()->consecutive : null,
            ];
        });

        return view('livewire.tenant.portal.client-quotes', [
            'quotes' => $quotes,
            'rows'   => $rows,
        ])->layout('layouts.app', ['header' => 'Portal de Clientes']);
    }
}
