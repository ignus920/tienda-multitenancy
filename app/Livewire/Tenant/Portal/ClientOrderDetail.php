<?php

namespace App\Livewire\Tenant\Portal;

use App\Traits\InteractsWithClientPortal;
use Livewire\Component;

class ClientOrderDetail extends Component
{
    use InteractsWithClientPortal;

    public int $remissionId;

    public function boot(): void
    {
        $this->ensureTenantConnection();
    }

    public function mount(int $remission): void
    {
        $this->ensureTenantConnection();
        // Verifica pertenencia; aborta 403 si no es de la empresa del cliente
        $this->findClientRemissionOrFail($remission);
        $this->remissionId = $remission;
    }

    public function render()
    {
        $remission = $this->findClientRemissionOrFail($this->remissionId);
        $remission->load(['quote.detalles', 'invoice', 'deliveryTypeModel']);

        $quote = $remission->quote;
        $lines = collect(optional($quote)->detalles ?? [])->map(function ($d) {
            $unit = (float) $d->value;
            $qty  = (float) $d->quantity;
            $subtotal = $unit * $qty;
            return [
                'description' => $d->description,
                'quantity'    => $qty,
                'unit'        => $unit,
                'subtotal'    => $subtotal,
            ];
        });

        $productsTotal = (float) optional($quote)->total;
        $flete = (float) optional($quote)->flete;
        $grandTotal = $productsTotal + $flete;

        $invoice = $remission->invoice;

        return view('livewire.tenant.portal.client-order-detail', [
            'remission'     => $remission,
            'badge'         => $this->orderStatusBadge($remission->status),
            'timeline'      => $this->buildOrderTimeline($remission),
            'lines'         => $lines,
            'productsTotal' => $productsTotal,
            'flete'         => $flete,
            'grandTotal'    => $grandTotal,
            'invoice'       => $invoice,
            'invoiceBadge'  => $invoice ? $this->invoicePaymentBadge($invoice->status_payment) : null,
            'deliveryTypeName' => optional($remission->deliveryTypeModel)->name,
        ])->layout('layouts.app', ['header' => 'Portal de Clientes']);
    }
}
