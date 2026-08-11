<?php

namespace App\Livewire\Tenant\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\Livewire\WithExport;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JustificationReport extends Component
{
    use WithPagination, WithExport;

    public $dateFrom;
    public $dateTo;
    public $selectedVendedor = '';
    public $search = '';
    public $perPage = 15;

    public $vendedores = [];

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    public function mount()
    {
        $this->ensureTenantConnection();
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        $this->loadVendedores();
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) {
            return $this->redirectRoute('tenant.select');
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            session()->forget('tenant_id');
            return $this->redirectRoute('tenant.select');
        }

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        
        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedVendedor()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
    }

    public function getQuery()
    {
        return DB::connection('tenant')->table('vnt_detail_quotes as dq')
            ->join('vnt_quotes as q', 'dq.quoteId', '=', 'q.id')
            ->join('inv_items as i', 'dq.itemId', '=', 'i.id')
            ->leftJoin('inv_items_dimensions as idim', function($join) {
                $join->on('i.id', '=', 'idim.item_id')
                     ->whereNull('idim.deleted_at');
            })
            ->leftJoin(config('database.connections.central.database') . '.users as u', 'q.userId', '=', 'u.id')
            ->whereNotNull('dq.justification')
            ->where('dq.justification', '!=', '')
            ->whereBetween(DB::raw('DATE(q.created_at)'), [$this->dateFrom, $this->dateTo])
            ->when($this->selectedVendedor, function($query) {
                return $query->where('q.userId', $this->selectedVendedor);
            })
            ->when($this->search, function($query) {
                return $query->where(function($q) {
                    $q->where('u.name', 'like', '%' . $this->search . '%')
                      ->orWhere('i.name', 'like', '%' . $this->search . '%')
                      ->orWhere('i.internal_code', 'like', '%' . $this->search . '%')
                      ->orWhere('q.consecutive', 'like', '%' . $this->search . '%')
                      ->orWhere('dq.justification', 'like', '%' . $this->search . '%');
                });
            })
            ->select([
                'q.consecutive as cotizacion',
                'q.created_at as fecha',
                'u.name as vendedor',
                'i.internal_code as codigo',
                'i.name as producto',
                'dq.quantity as cantidad',
                'idim.quntityxbox as unidades_x_caja',
                'dq.justification as justificacion'
            ])
            ->orderBy('q.created_at', 'desc');
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $reportData = $this->getQuery()->paginate($this->perPage);

        return view('livewire.tenant.reports.justification-report', [
            'reportData' => $reportData
        ])->layout('layouts.app', ['header' => 'Reporte de Justificaciones de Cantidad']);
    }

    /**
     * Métodos para soporte de exportación (Excel/CSV)
     */
    protected function getExportData()
    {
        return $this->getQuery()->get();
    }

    protected function getExportHeadings(): array
    {
        return ['Cotización', 'Fecha', 'Vendedor', 'Código', 'Producto', 'Cantidad', 'Unidades x Caja', 'Justificación'];
    }

    protected function getExportMapping($item = null)
    {
        if (!$item) return [];
        return (array)$item;
    }

    protected function getExportFilename(): string
    {
        return 'justificaciones_cantidad_' . now()->format('Ymd_His');
    }
}
