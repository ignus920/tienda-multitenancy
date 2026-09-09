<?php

namespace App\Livewire\Tenant\Portal;

use App\Traits\InteractsWithClientPortal;
use Livewire\Component;
use Livewire\WithPagination;

class ClientOrders extends Component
{
    use InteractsWithClientPortal;
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public int $perPage = 12;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function boot(): void
    {
        $this->ensureTenantConnection();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    public function render()
    {
        $orders = $this->clientRemissionQuery()
            ->with(['quote.detalles', 'invoice'])
            ->when($this->search !== '', fn ($q) => $q->where('consecutive', 'like', '%' . trim($this->search) . '%'))
            ->when($this->statusFilter === 'in_progress', fn ($q) => $q->whereIn('status', ['REGISTRADO', 'ALISTAMIENTO', 'EMPACADO', 'EN RECORRIDO', 'ENTREGADO A RUTA', 'EN RUTA']))
            ->when($this->statusFilter === 'delivered', fn ($q) => $q->where('status', 'ENTREGADO'))
            ->when($this->statusFilter === 'cancelled', fn ($q) => $q->whereIn('status', ['ANULADO', 'DEVUELTO', 'VENCIDO']))
            ->latest('id')
            ->paginate($this->perPage);

        $rows = collect($orders->items())->map(fn ($r) => [
            'id'          => $r->id,
            'consecutive' => $r->consecutive,
            'date'        => $r->created_at,
            'items'       => (int) optional($r->quote)->detalles?->count(),
            'total'       => (float) optional($r->quote)->total + (float) optional($r->quote)->flete,
            'badge'       => $this->orderStatusBadge($r->status),
            'delivery'    => $r->deliveryDate,
            'address'     => $r->observations_delivery,
            'invoice_no'  => optional($r->invoice)->invoiceNumber ?: optional($r->invoice)->consecutive,
        ]);

        return view('livewire.tenant.portal.client-orders', [
            'orders' => $orders,
            'rows'   => $rows,
        ])->layout('layouts.app', ['header' => 'Portal de Clientes']);
    }
}
