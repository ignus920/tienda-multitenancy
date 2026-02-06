<?php

namespace App\Livewire\Tenant\Invoices;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Invoices\VntInvoices;
use App\Models\Tenant\Invoices\VntInvoicesXsales;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Invoices extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 12;
    public $sortField = 'vnt_invoices.invoiceNumber';
    public $sortDirection = 'desc';

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
        $this->resetPage();
    }

    public function mount()
    {
        $this->ensureTenantConnection();
    }

    public function render()
    {
        try {
            $centralDbName = config('database.connections.central.database');
            $this->ensureTenantConnection();

            // Subconsulta para totales de detalles de remisión
            $remissionTotals = DB::connection('tenant')->table("inv_detail_remissions")
                ->select(
                    "invoiceId",
                    DB::raw("SUM(value * quantity) as total_sin_impuestos"),
                    DB::raw("SUM((value + (value * tax / 100)) * quantity) as total_con_impuestos")
                )
                ->whereNotNull("invoiceId")
                ->groupBy("invoiceId");

            // Subconsulta para totales de detalles de cotización
            $quoteTotals = DB::connection('tenant')->table("vnt_detail_quotes as dq")
                ->select(
                    "ixs.invoiceId",
                    DB::raw("SUM(dq.value * dq.quantity) as total_sin_impuestos"),
                    DB::raw("SUM((dq.value + (dq.value * dq.tax / 100)) * dq.quantity) as total_con_impuestos")
                )
                ->join("vnt_invoicesXsales as ixs", "dq.quoteId", "=", "ixs.quoteId")
                ->whereNotNull("ixs.invoiceId")
                ->groupBy("ixs.invoiceId");

            // Subconsulta para GROUP_CONCAT de consecutivos de remisión
            $remissionConsecutives = DB::connection('tenant')->table("vnt_invoicesXsales as s_sub")
                ->select(
                    "s_sub.invoiceId",
                    DB::raw("GROUP_CONCAT(r_sub.consecutive ORDER BY r_sub.consecutive SEPARATOR ', ') as remission_consecutive")
                )
                ->join("inv_remissions as r_sub", "s_sub.remissionId", "=", "r_sub.id")
                ->groupBy("s_sub.invoiceId");

            $query = VntInvoices::query()
                ->select([
                    'vnt_invoices.id',
                    'vnt_invoices.consecutive',
                    'vnt_invoices.status',
                    'vnt_invoices.status_payment',
                    'vnt_invoices.api_data_id',
                    'vnt_invoices.api_data_id_pay',
                    'vnt_invoices.partialPayment',
                    'vnt_invoices.quoteId',
                    'vnt_invoices.warehouseId',
                    'vnt_invoices.remission',
                    'vnt_invoices.creditNoteId',
                    'vnt_invoices.invoiceNumber',
                    'vnt_invoices.retentionFuente',
                    'vnt_invoices.retentionIca',
                    'vnt_invoices.retentionIva',
                    'vnt_invoices.creditNote',
                    'vnt_invoices.orderNumber',
                    'vnt_invoices.created_at',
                    'vnt_invoices.updated_at',
                    'vnt_invoices.deleted_at',
                    DB::raw("MAX(remission_consecutives.remission_consecutive) as remission_consecutive"),
                    DB::raw("MAX(COALESCE(wr.name, wd.name)) as warehouse_name"),
                    DB::raw("MAX(CONCAT(COALESCE(u.name, ''), ' ', COALESCE(u.name, ''))) AS seller"),
                    DB::raw("MAX(CONCAT(COALESCE(c.firstName, ''), ' ', COALESCE(c.secondName, ''), ' ', COALESCE(c.lastName, ''), ' ', COALESCE(c.secondLastName, ''))) AS client_name"),
                    DB::raw("MAX(COALESCE(dr_totals.total_sin_impuestos, dq_totals.total_sin_impuestos, 0)) AS total_sin_impuestos"),
                    DB::raw("MAX(COALESCE(dr_totals.total_con_impuestos, dq_totals.total_con_impuestos, 0)) AS total_con_impuestos"),
                    DB::raw("MAX(IF(s.remissionId IS NOT NULL, 'REMISIONADA', 'COTIZADA')) as tipo_factura"),
                ])
                ->join("vnt_invoicesXsales as s", "s.invoiceId", "=", "vnt_invoices.id")
                // Joins para la ruta de Remisión
                ->leftJoin("inv_remissions as r", "s.remissionId", "=", "r.id")
                ->leftJoin("inv_store as wr", "r.warehouseId", "=", "wr.id")
                // Joins para la ruta de Cotización (puede venir de una remisión o directa)
                ->leftJoin("vnt_quotes as qr", "r.quoteId", "=", "qr.id") // Cotización vía Remisión
                ->leftJoin("vnt_quotes as qd", "s.quoteId", "=", "qd.id") // Cotización Directa
                ->leftJoin("inv_store as wd", "qd.warehouseId", "=", "wd.id")
                // Joins para datos de Cliente y Vendedor (usando COALESCE para tomar de cualquiera de las dos rutas)
                ->leftJoin("vnt_contacts as c", "c.id", "=", DB::raw("COALESCE(qr.customerId, qd.customerId)"))
                ->leftJoin(DB::raw("{$centralDbName}.users as u"), "u.id", "=", DB::raw("COALESCE(qr.userId, qd.userId)"))
                // Joins con las subconsultas
                ->leftJoinSub($remissionTotals, "dr_totals", "vnt_invoices.id", "=", "dr_totals.invoiceId")
                ->leftJoinSub($quoteTotals, "dq_totals", "vnt_invoices.id", "=", "dq_totals.invoiceId")
                ->leftJoinSub($remissionConsecutives, "remission_consecutives", "vnt_invoices.id", "=", "remission_consecutives.invoiceId")
                ->groupBy([
                    "vnt_invoices.id",
                    "vnt_invoices.consecutive",
                    "vnt_invoices.status",
                    "vnt_invoices.status_payment",
                    "vnt_invoices.api_data_id",
                    "vnt_invoices.api_data_id_pay",
                    "vnt_invoices.partialPayment",
                    "vnt_invoices.quoteId",
                    "vnt_invoices.warehouseId",
                    "vnt_invoices.remission",
                    "vnt_invoices.creditNoteId",
                    "vnt_invoices.invoiceNumber",
                    "vnt_invoices.retentionFuente",
                    "vnt_invoices.retentionIca",
                    "vnt_invoices.retentionIva",
                    "vnt_invoices.creditNote",
                    "vnt_invoices.orderNumber",
                    "vnt_invoices.created_at",
                    "vnt_invoices.updated_at",
                    "vnt_invoices.deleted_at"
                ]);

            // Aplicar búsqueda
            $query->when($this->search, function ($q) {
                $search = '%' . $this->search . '%';
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('vnt_invoices.invoiceNumber', 'like', $search)
                        ->orWhere(DB::raw("CONCAT(COALESCE(c.firstName, ''), ' ', COALESCE(c.lastName, ''))"), 'like', $search)
                        ->orWhere(DB::raw("CONCAT(COALESCE(u.name, ''), ' ', COALESCE(u.name, ''))"), 'like', $search);
                });
                // Usar HAVING para campos agregados
                $q->havingRaw("MAX(remission_consecutives.remission_consecutive) LIKE ?", [$search]);
            });

            // Aplicar ordenamiento
            $query->orderBy($this->sortField, $this->sortDirection);

            $invoices = $query->paginate($this->perPage);

            return view('livewire.tenant.invoices.invoices', [
                'invoices' => $invoices
            ])->layout('layouts.app', ['header' => 'Gestión de Facturas']);
        } catch (\Exception $e) {
            Log::error('❌ Error en la consulta de facturas: ' . $e->getMessage() . ' en la línea ' . $e->getLine());
            // Opcional: retornar una vista de error o una colección vacía para no romper la UI
            return view('livewire.tenant.invoices.invoices', [
                'invoices' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage)
            ])->layout('layouts.app', ['header' => 'Gestión de Facturas']);
        }
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
