<?php

namespace App\Livewire\Tenant\Reports;

use Livewire\Component;
use App\Traits\HasCompanyConfiguration;
use App\Traits\Livewire\HasDynamicButtons;
use Illuminate\Support\Facades\DB;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;
use App\Models\Auth\Tenant;

use Livewire\WithPagination;
use App\Traits\Livewire\WithExport;

class ReportsList extends Component
{
    use HasCompanyConfiguration, HasDynamicButtons, WithPagination, WithExport;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public $dateFrom;
    public $dateTo;
    public $activeReport = null;
    public $reportTitle = '';
    public $vendedores = [];
    public $selectedVendedor = '';

    // Propiedades para Reporte Cotización x Producto (Multiselección)
    public $selectedItemCodes = [];
    public $itemDetails = [];

    // Propiedades para Reporte Productos x Cliente (Multiselección)
    public $selectedCustomerIds = [];
    public $customerDetails = [];

    // Propiedades para Reporte Pedido x Estado
    public $selectedEstado = '';

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function mount()
    {
        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();

        // Fechas por defecto: Mes actual
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function clearFilters()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        $this->activeReport = null;
        $this->reportTitle = '';
        $this->selectedVendedor = '';
        $this->selectedItemCodes = [];
        $this->itemDetails = [];
        $this->selectedCustomerIds = [];
        $this->customerDetails = [];
        $this->selectedEstado = '';
        $this->search = '';
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActiveReport()
    {
        $this->resetPage();
    }

    public function updatedActiveReport()
    {
        $this->resetPage();
        if ($this->activeReport === 'ventas_vendedor') {
            $this->loadVendedores();
        }
    }

    public function updatedSelectedVendedor()
    {
        $this->resetPage();
    }

    public function updatedSelectedEstado()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->refreshActiveReport();
    }

    public function updatedDateTo()
    {
        $this->refreshActiveReport();
    }

    public function updatedSelectedItemCodes()
    {
        $this->loadItemDetails();
    }

    public function updatedSelectedCustomerIds()
    {
        $this->loadCustomerDetails();
    }

    private function refreshActiveReport()
    {
        $this->resetPage();
        if ($this->activeReport === 'ventas_vendedor') {
            $this->loadVendedores();
        }
    }

    private function loadVendedores()
    {
        $this->vendedores = DB::connection('central')->table('users')
            ->whereIn('id', DB::connection('tenant')->table('vnt_quotes')->distinct()->pluck('userId'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    /**
     * Informe de ventas por vendedor
     */
    public function loadVentasVendedor()
    {
        $this->activeReport = 'ventas_vendedor';
        $this->reportTitle = 'Informe de ventas por vendedor y por precio';
        $this->loadVendedores();
        $this->resetPage();
    }

    private function getVentasVendedorQuery()
    {
        return DB::connection('tenant')->table('inv_remissions as r')
            ->join('vnt_quotes as q', 'r.quoteId', '=', 'q.id')
            ->join('vnt_detail_quotes as dq', 'q.id', '=', 'dq.quoteId')
            ->join('inv_items as i', 'dq.itemId', '=', 'i.id')
            ->leftJoin(config('database.connections.central.database') . '.users as u', 'q.userId', '=', 'u.id')
            ->leftJoin('vnt_invoicesXsales as ivs', 'r.id', '=', 'ivs.remissionId')
            ->leftJoin('vnt_invoices as inv', 'ivs.invoiceId', '=', 'inv.id')
            ->leftJoin('vnt_method_payments as mp', 'r.methodPaymentId', '=', 'mp.id')
            ->whereBetween(DB::raw('DATE(r.created_at)'), [$this->dateFrom, $this->dateTo])
            ->when($this->selectedVendedor, function($query) {
                return $query->where('q.userId', $this->selectedVendedor);
            })
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('u.name', 'like', '%' . $this->search . '%')
                      ->orWhere('q.consecutive', 'like', '%' . $this->search . '%')
                      ->orWhere('r.consecutive', 'like', '%' . $this->search . '%')
                      ->orWhere('i.name', 'like', '%' . $this->search . '%');
                });
            })
            ->select([
                'u.name as vendedor',
                'q.consecutive as cot',
                'r.status as estado',
                'r.consecutive as remission',
                'inv.invoiceNumber as factura',
                'r.created_at as fecha',
                DB::raw("CONCAT(i.internal_code, ' - ', i.name) as descripcion"),
                'dq.quantity as cantidad',
                'dq.value as precio_con_iva',
                DB::raw('ROUND(dq.quantity * (dq.value / (1 + dq.tax/100)), 2) as subtotal'),
                DB::raw('ROUND(dq.quantity * dq.value, 2) as total'),
                'dq.price_label as clasificacion',
                'mp.name as forma_pago'
            ]);
    }

    /**
     * Informe cotización x producto
     */
    public function loadCotizacionesProducto()
    {
        $this->activeReport = 'cotizaciones_producto';
        $this->reportTitle = 'Informe cotización x producto';
        $this->resetPage();
    }

    private function getCotizacionesProductoQuery()
    {
        return DB::connection('tenant')->table('vnt_detail_quotes as dq')
            ->join('vnt_quotes as q', 'dq.quoteId', '=', 'q.id')
            ->join('inv_items as i', 'dq.itemId', '=', 'i.id')
            ->leftJoin('inv_remissions as r', 'q.id', '=', 'r.quoteId')
            ->whereBetween(DB::raw('DATE(q.created_at)'), [$this->dateFrom, $this->dateTo])
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('i.internal_code', 'like', '%' . $this->search . '%')
                      ->orWhere('i.name', 'like', '%' . $this->search . '%');
                });
            })
            ->select([
                'i.internal_code as codigo',
                'i.name as producto',
                DB::raw('COUNT(DISTINCT q.id) as cotizaciones'),
                DB::raw('COUNT(DISTINCT r.id) as pedidos'),
                DB::raw('ROUND(IF(COUNT(DISTINCT q.id) > 0, (COUNT(DISTINCT r.id) / COUNT(DISTINCT q.id)) * 100, 0), 2) as porcentaje_pedidos'),
                DB::raw('SUM(dq.quantity) as unidades'),
                DB::raw('SUM(IF(r.id IS NOT NULL, dq.quantity, 0)) as unidades_pedidas')
            ])
            ->groupBy('i.internal_code', 'i.name')
            ->orderBy('cotizaciones', 'desc');
    }

    /**
     * Carga el desglose detallado para los productos seleccionados
     */
    public function loadItemDetails()
    {
        if (empty($this->selectedItemCodes)) {
            $this->itemDetails = [];
            return;
        }

        $this->itemDetails = DB::connection('tenant')->table('vnt_detail_quotes as dq')
            ->join('vnt_quotes as q', 'dq.quoteId', '=', 'q.id')
            ->join('inv_items as i', 'dq.itemId', '=', 'i.id')
            ->join('vnt_contacts as c', 'q.customerId', '=', 'c.warehouseId')
            ->leftJoin('vnt_warehouses as w', 'c.warehouseId', '=', 'w.id')
            ->leftJoin('vnt_companies as comp', 'w.companyId', '=', 'comp.id')
            ->leftJoin(config('database.connections.central.database') . '.users as u', 'q.userId', '=', 'u.id')
            ->whereIn('i.internal_code', $this->selectedItemCodes)
            ->whereBetween(DB::raw('DATE(q.created_at)'), [$this->dateFrom, $this->dateTo])
            ->select([
                'i.internal_code as codigo',
                'i.name as producto_nombre',
                'q.created_at as fecha',
                'q.consecutive as cotizacion',
                DB::raw("CASE 
                    WHEN comp.businessName IS NOT NULL AND comp.businessName != '' THEN comp.businessName 
                    ELSE CONCAT(COALESCE(c.firstName,''), ' ', COALESCE(c.lastName,'')) 
                END as cliente_nombre"),
                'u.name as asesor',
                'comp.identification as identidad',
                'dq.quantity as cantidad',
                'q.status as estado',
                'dq.price_label as clasificacion'
            ])
            ->orderBy('i.internal_code')
            ->orderBy('q.created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Informe productos x cliente
     */
    public function loadProductosCliente()
    {
        $this->activeReport = 'productos_cliente';
        $this->reportTitle = 'Informe productos x cliente';
        $this->resetPage();
    }

    private function getProductosClienteQuery()
    {
        return DB::connection('tenant')->table('vnt_quotes as q')
            ->join('vnt_detail_quotes as dq', 'q.id', '=', 'dq.quoteId')
            ->join('vnt_contacts as c', 'q.customerId', '=', 'c.warehouseId')
            ->leftJoin('inv_remissions as r', 'q.id', '=', 'r.quoteId')
            ->whereBetween(DB::raw('DATE(q.created_at)'), [$this->dateFrom, $this->dateTo])
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('c.firstName', 'like', '%' . $this->search . '%')
                      ->orWhere('c.lastName', 'like', '%' . $this->search . '%');
                });
            })
            ->select([
                'c.warehouseId as id',
                DB::raw("CONCAT(COALESCE(c.firstName,''), ' ', COALESCE(c.lastName,'')) as nombre"),
                DB::raw('COUNT(DISTINCT q.id) as cotizaciones'),
                DB::raw('COUNT(DISTINCT r.id) as pedidos'),
                DB::raw('SUM(dq.quantity * dq.value) as v_cotizados'),
                DB::raw('SUM(IF(r.id IS NOT NULL, dq.quantity * dq.value, 0)) as v_pedidos'),
                DB::raw('ROUND(IF(COUNT(DISTINCT q.id) > 0, (COUNT(DISTINCT r.id) / COUNT(DISTINCT q.id)) * 100, 0), 2) as porcentaje_pedidos')
            ])
            ->groupBy('c.warehouseId', 'c.firstName', 'c.lastName')
            ->orderBy('v_pedidos', 'desc');
    }

    /**
     * Carga el desglose de productos para los clientes seleccionados
     */
    public function loadCustomerDetails()
    {
        if (empty($this->selectedCustomerIds)) {
            $this->customerDetails = [];
            return;
        }

        $this->customerDetails = DB::connection('tenant')->table('vnt_detail_quotes as dq')
            ->join('vnt_quotes as q', 'dq.quoteId', '=', 'q.id')
            ->join('inv_items as i', 'dq.itemId', '=', 'i.id')
            ->join('vnt_contacts as c', 'q.customerId', '=', 'c.warehouseId')
            ->leftJoin('inv_remissions as r', 'q.id', '=', 'r.quoteId')
            ->whereIn('c.warehouseId', $this->selectedCustomerIds)
            ->whereBetween(DB::raw('DATE(q.created_at)'), [$this->dateFrom, $this->dateTo])
            ->select([
                'c.warehouseId as cliente_id',
                DB::raw("CONCAT(COALESCE(c.firstName,''), ' ', COALESCE(c.lastName,'')) as cliente_nombre"),
                'i.name as descripcion',
                'i.internal_code as codigo',
                DB::raw('COUNT(DISTINCT q.id) as cotizacion'),
                DB::raw('COUNT(DISTINCT r.id) as pedidos'),
                DB::raw('SUM(dq.quantity) as cotizados'),
                DB::raw('SUM(IF(r.id IS NOT NULL, dq.quantity, 0)) as pedido')
            ])
            ->groupBy('c.warehouseId', 'c.firstName', 'c.lastName', 'i.id', 'i.name', 'i.internal_code')
            ->orderBy('cliente_nombre')
            ->orderBy('pedido', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Informe Pedido x estado
     */
    public function loadPedidosEstado()
    {
        $this->activeReport = 'pedidos_estado';
        $this->reportTitle = 'Informe Pedido x estado';
        $this->resetPage();
    }

    private function getPedidosEstadoQuery()
    {
        return DB::connection('tenant')->table('inv_remissions as r')
            ->join('vnt_quotes as q', 'r.quoteId', '=', 'q.id')
            ->join('vnt_contacts as c', 'q.customerId', '=', 'c.warehouseId')
            ->leftJoin('vnt_invoicesXsales as ivs', 'r.id', '=', 'ivs.remissionId')
            ->leftJoin('vnt_invoices as inv', 'ivs.invoiceId', '=', 'inv.id')
            ->leftJoin(config('database.connections.central.database') . '.users as u', 'r.userId', '=', 'u.id')
            ->whereBetween(DB::raw('DATE(r.created_at)'), [$this->dateFrom, $this->dateTo])
            ->when($this->selectedEstado, function($query) {
                return $query->where('r.status', $this->selectedEstado);
            })
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('r.consecutive', 'like', '%' . $this->search . '%')
                      ->orWhere('c.firstName', 'like', '%' . $this->search . '%')
                      ->orWhere('c.lastName', 'like', '%' . $this->search . '%')
                      ->orWhere('q.consecutive', 'like', '%' . $this->search . '%');
                });
            })
            ->select([
                'r.consecutive',
                'r.created_at as fecha',
                DB::raw("CONCAT(COALESCE(c.firstName,''), ' ', COALESCE(c.lastName,'')) as cliente"),
                'q.consecutive as erp_quote',
                'r.status',
                'r.observations as entrega',
                'inv.invoiceNumber as factura',
                'u.name as creator',
                DB::raw('CASE 
                    WHEN r.status = 1 THEN "Alistamiento"
                    WHEN r.status = 2 THEN "Empacado"
                    WHEN r.status = 3 THEN "En ruta"
                    WHEN r.status = 4 THEN "Entregado"
                    WHEN r.status = 5 THEN "Imposibilidad"
                    WHEN r.status = 6 THEN "Anulado"
                    WHEN r.status = 7 THEN "Cartera"
                    ELSE "Otro"
                END as estado_texto')
            ])
            ->orderBy('r.created_at', 'desc');
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $reportData = collect();
        if ($this->activeReport) {
            $method = 'get' . str_replace('_', '', ucwords($this->activeReport, '_')) . 'Query';
            if (method_exists($this, $method)) {
                $reportData = $this->$method()->paginate($this->perPage);
            }
        }

        return view('livewire.tenant.reports.reports-list', [
            'reportData' => $reportData
        ])->layout('layouts.app', ['header' => 'Informes']);
    }

    /**
     * Implementación de WithExport
     */
    protected function getExportData()
    {
        if (!$this->activeReport) return collect();

        $method = 'get' . str_replace('_', '', ucwords($this->activeReport, '_')) . 'Query';
        if (method_exists($this, $method)) {
            return $this->$method()->get();
        }

        return collect();
    }

    protected function getExportHeadings(): array
    {
        switch ($this->activeReport) {
            case 'ventas_vendedor':
                return ['Vendedor', 'Cotización', 'Estado', 'Remisión', 'Factura', 'Fecha', 'Descripción', 'Cant', 'Precio con IVA', 'Subtotal', 'Total', 'Clasificación', 'Forma Pago'];
            case 'cotizaciones_producto':
                return ['Código', 'Producto', 'Cotizaciones', 'Pedidos', '% Efectividad', 'Unidades Cotizadas', 'Unidades Pedidas'];
            case 'productos_cliente':
                return ['ID', 'Cliente', 'Cotizaciones', 'Pedidos', 'Valor Cotizado', 'Valor Pedido', '% Efectividad'];
            case 'pedidos_estado':
                return ['Consecutivo', 'Fecha', 'Cliente', 'Cotiz ERP', 'Estado', 'Tipo Entrega', 'Factura', 'Creador'];
            default:
                return [];
        }
    }

    protected function getExportMapping($item = null)
    {
        if (!$item) return [];

        return (array)$item;
    }

    protected function getExportFilename(): string
    {
        return 'informe_' . $this->activeReport . '_' . now()->format('Ymd_His');
    }

    /**
     * Asegurar que la conexión del tenant esté establecida
     * Verifica que exista un tenant en sesión y lo inicializa
     */
    public function ensureTenantConnection()
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
