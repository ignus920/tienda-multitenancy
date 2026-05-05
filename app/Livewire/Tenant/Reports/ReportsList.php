<?php

namespace App\Livewire\Tenant\Reports;

use Livewire\Component;
use App\Traits\HasCompanyConfiguration;
use App\Traits\Livewire\HasDynamicButtons;
use Illuminate\Support\Facades\DB;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;
use App\Models\Auth\Tenant;

class ReportsList extends Component
{
    use HasCompanyConfiguration, HasDynamicButtons;

    public $dateFrom;
    public $dateTo;
    public $activeReport = null;
    public $reportData = [];
    public $reportTitle = '';
    public $vendedores = [];
    public $selectedVendedor = '';

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
        $this->reportData = [];
        $this->reportTitle = '';
        $this->selectedVendedor = '';
    }

    public function updatedSelectedVendedor()
    {
        if ($this->activeReport == 'ventas_vendedor') {
            $this->loadVentasVendedor();
        }
    }

    public function updatedDateFrom()
    {
        $this->refreshActiveReport();
    }

    public function updatedDateTo()
    {
        $this->refreshActiveReport();
    }

    private function refreshActiveReport()
    {
        if ($this->activeReport) {
            $method = 'load' . str_replace('_', '', ucwords($this->activeReport, '_'));
            if (method_exists($this, $method)) {
                $this->$method();
            }
        }
    }

    /**
     * Informe de ventas por vendedor
     */
    public function loadVentasVendedor()
    {
        $this->activeReport = 'ventas_vendedor';
        $this->reportTitle = 'Informe de ventas por vendedor';
        
        $this->vendedores = DB::connection('central')->table('users')
            ->whereIn('id', DB::connection('tenant')->table('vnt_quotes')->distinct()->pluck('userId'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->reportData = DB::connection('tenant')->table('inv_remissions as r')
            ->join('vnt_quotes as q', 'r.quoteId', '=', 'q.id')
            ->join('vnt_detail_quotes as dq', 'q.id', '=', 'dq.quoteId')
            ->join('inv_items as i', 'dq.itemId', '=', 'i.id')
            ->leftJoin(config('database.connections.central.database') . '.users as u', 'q.userId', '=', 'u.id')
            ->whereBetween(DB::raw('DATE(r.created_at)'), [$this->dateFrom, $this->dateTo])
            ->when($this->selectedVendedor, function($query) {
                return $query->where('q.userId', $this->selectedVendedor);
            })
            ->select([
                'q.consecutive as cot',
                'r.consecutive as remission',
                'r.created_at as fecha',
                'u.name as vendedor',
                'i.name as producto',
                'i.internal_code as codigo',
                'dq.quantity as cantidad',
                'dq.value as precio_con_iva',
                DB::raw('ROUND(dq.quantity * (dq.value / (1 + dq.tax/100)), 2) as subtotal'),
                DB::raw('ROUND(dq.quantity * dq.value, 2) as total')
            ])
            ->get()
            ->toArray();
    }

    /**
     * Informe cotización x producto
     */
    public function loadCotizacionesProducto()
    {
        $this->activeReport = 'cotizaciones_producto';
        $this->reportTitle = 'Informe cotización x producto';

        $this->reportData = DB::connection('tenant')->table('vnt_detail_quotes as dq')
            ->join('vnt_quotes as q', 'dq.quoteId', '=', 'q.id')
            ->join('inv_items as i', 'dq.itemId', '=', 'i.id')
            ->whereBetween(DB::raw('DATE(q.created_at)'), [$this->dateFrom, $this->dateTo])
            ->select([
                'i.id',
                'i.name as producto',
                'i.internal_code as codigo',
                DB::raw('COUNT(DISTINCT q.id) as total_cotizaciones'),
                DB::raw('SUM(dq.quantity) as cantidad_total'),
                DB::raw('SUM(dq.quantity * dq.value) as valor_total')
            ])
            ->groupBy('i.id', 'i.name', 'i.internal_code')
            ->orderBy('total_cotizaciones', 'desc')
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

        $this->reportData = DB::connection('tenant')->table('vnt_quotes as q')
            ->join('vnt_detail_quotes as dq', 'q.id', '=', 'dq.quoteId')
            ->join('inv_items as i', 'dq.itemId', '=', 'i.id')
            ->join('vnt_contacts as c', 'q.customerId', '=', 'c.warehouseId') // Mapeo customerId -> warehouseId del contacto
            ->whereBetween(DB::raw('DATE(q.created_at)'), [$this->dateFrom, $this->dateTo])
            ->select([
                'c.firstName', 'c.lastName',
                DB::raw("CONCAT(COALESCE(c.firstName,''), ' ', COALESCE(c.lastName,'')) as cliente"),
                'i.name as producto',
                'i.internal_code as codigo',
                DB::raw('SUM(dq.quantity) as cantidad'),
                DB::raw('SUM(dq.quantity * dq.value) as total')
            ])
            ->groupBy('c.id', 'i.id', 'c.firstName', 'c.lastName', 'i.name', 'i.internal_code')
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

        $this->reportData = DB::connection('tenant')->table('inv_remissions as r')
            ->join('vnt_quotes as q', 'r.quoteId', '=', 'q.id')
            ->join('vnt_contacts as c', 'q.customerId', '=', 'c.warehouseId')
            ->whereBetween(DB::raw('DATE(r.created_at)'), [$this->dateFrom, $this->dateTo])
            ->select([
                'r.consecutive',
                'r.created_at as fecha',
                DB::raw("CONCAT(COALESCE(c.firstName,''), ' ', COALESCE(c.lastName,'')) as cliente"),
                'r.status',
                DB::raw('CASE 
                    WHEN r.status = 1 THEN "Registrado"
                    WHEN r.status = 2 THEN "Confirmado"
                    WHEN r.status = 7 THEN "Almanecenamiento"
                    ELSE "Desconocido"
                END as estado_texto')
            ])
            ->orderBy('r.created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.tenant.reports.reports-list')
            ->layout('layouts.app', ['header' => 'Informes']);
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
