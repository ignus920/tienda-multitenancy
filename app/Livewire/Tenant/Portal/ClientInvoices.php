<?php

namespace App\Livewire\Tenant\Portal;

use App\Traits\InteractsWithClientPortal;
use Livewire\Component;
use Livewire\WithPagination;

class ClientInvoices extends Component
{
    use InteractsWithClientPortal;
    use WithPagination;

    public string $search = '';
    public string $paymentFilter = '';
    public int $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'paymentFilter' => ['except' => ''],
    ];

    public function boot(): void
    {
        $this->ensureTenantConnection();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'paymentFilter']);
        $this->resetPage();
    }

    public function render()
    {
        $invoices = $this->clientInvoiceQuery()
            ->with(['quote.detalles', 'quote.remissions'])
            ->when($this->search !== '', function ($q) {
                $s = '%' . trim($this->search) . '%';
                $q->where(fn ($w) => $w->where('invoiceNumber', 'like', $s)->orWhere('consecutive', 'like', $s));
            })
            ->when($this->paymentFilter === 'pending', fn ($q) => $q->where(function ($w) {
                $w->whereNull('status_payment')->orWhereNotIn('status_payment', ['PAGADO', 'ANULADO']);
            }))
            ->when($this->paymentFilter === 'paid', fn ($q) => $q->where('status_payment', 'PAGADO'))
            ->latest('id')
            ->paginate($this->perPage);

        $rows = collect($invoices->items())->map(function ($inv) {
            $quote = $inv->quote;
            $total = (float) optional($quote)->total + (float) optional($quote)->flete;
            $remission = optional($quote)->remissions?->first();
            return [
                'id'          => $inv->id,
                'number'      => $inv->invoiceNumber ?: ('N° ' . $inv->consecutive),
                'date'        => $inv->created_at,
                'total'       => $total,
                'badge'       => $this->invoicePaymentBadge($inv->status_payment),
                'order_no'    => optional($remission)->consecutive,
                'order_id'    => optional($remission)->id,
                'downloadable'=> !empty($inv->api_data_id) && $inv->status === 'FACTURADO',
            ];
        });

        return view('livewire.tenant.portal.client-invoices', [
            'invoices' => $invoices,
            'rows'     => $rows,
        ])->layout('layouts.app', ['header' => 'Portal de Clientes']);
    }
}
