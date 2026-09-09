<?php

namespace App\Livewire\Tenant\Portal;

use App\Traits\InteractsWithClientPortal;
use App\Models\Tenant\Customer\VntCompany;
use Livewire\Component;

class ClientDashboard extends Component
{
    use InteractsWithClientPortal;

    public function boot(): void
    {
        $this->ensureTenantConnection();
    }

    public function render()
    {
        $companyId = $this->clientCompanyId();
        $company = VntCompany::find($companyId);
        $companyName = $company ? $company->customer_name : 'Cliente';

        $inProgressStatuses = ['REGISTRADO', 'ALISTAMIENTO', 'EMPACADO', 'EN RECORRIDO', 'ENTREGADO A RUTA', 'EN RUTA'];

        $ordersInProgress = (clone $this->clientRemissionQuery())
            ->whereIn('status', $inProgressStatuses)
            ->count();

        $totalOrders = (clone $this->clientRemissionQuery())->count();

        $nextDelivery = (clone $this->clientRemissionQuery())
            ->whereIn('status', $inProgressStatuses)
            ->whereNotNull('deliveryDate')
            ->orderBy('deliveryDate')
            ->first();

        $unpaidInvoices = (clone $this->clientInvoiceQuery())
            ->where('status', '!=', 'ANULADO')
            ->where(function ($q) {
                $q->whereNull('status_payment')->orWhereNotIn('status_payment', ['PAGADO', 'ANULADO']);
            })
            ->with('quote.detalles')
            ->get();

        $unpaidTotal = $unpaidInvoices->sum(fn ($inv) => (float) optional($inv->quote)->total + (float) optional($inv->quote)->flete);

        $recentOrders = (clone $this->clientRemissionQuery())
            ->with(['quote.detalles', 'invoice'])
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn ($r) => [
                'id'         => $r->id,
                'consecutive'=> $r->consecutive,
                'date'       => $r->created_at,
                'items'      => (int) optional($r->quote)->detalles?->count(),
                'total'      => (float) optional($r->quote)->total + (float) optional($r->quote)->flete,
                'badge'      => $this->orderStatusBadge($r->status),
                'delivery'   => $r->deliveryDate,
                'invoice_no' => optional($r->invoice)->invoiceNumber ?: optional($r->invoice)->consecutive,
            ]);

        return view('livewire.tenant.portal.client-dashboard', [
            'companyName'     => $companyName,
            'ordersInProgress'=> $ordersInProgress,
            'totalOrders'     => $totalOrders,
            'nextDelivery'    => $nextDelivery,
            'unpaidCount'     => $unpaidInvoices->count(),
            'unpaidTotal'     => $unpaidTotal,
            'recentOrders'    => $recentOrders,
        ])->layout('layouts.app', ['header' => 'Portal de Clientes']);
    }
}
