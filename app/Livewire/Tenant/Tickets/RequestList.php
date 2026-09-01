<?php

namespace App\Livewire\Tenant\Tickets;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Tickets\TickRequest;
use App\Models\Tenant\Tickets\TickDepartment;
use App\Models\Tenant\Tickets\TickStatus;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Carbon\Carbon;

class RequestList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    
    // Filtros
    public $departmentId = '';
    public $supplierIdFilter = '';
    public $dateFrom;
    public $dateTo;
    public $selectedStatus = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'departmentId' => ['except' => ''],
        'supplierIdFilter' => ['except' => ''],
        'selectedStatus' => ['except' => null],
        'type' => ['except' => 'internal'],
    ];

    public $type = 'internal'; // 'internal' o 'supplier'

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $user = auth()->user();
        $isSupplier = $user && $user->profile_id == 17;

        // Consultas para los estados (Dashboard)
        $statuses = TickStatus::whereNotIn('name', ['Reactivado', 'Reactivated'])->orderBy('order')->get();
        $allStats = TickRequest::selectRaw('status_id, count(*) as total')
            ->when($isSupplier, function($q) use ($user) {
                return $q->where('supplier_id', $user->id);
            })
            ->when(!$isSupplier && $this->type === 'supplier', function($q) {
                return $q->whereNotNull('supplier_id');
            })
            ->when(!$isSupplier && $this->type === 'internal', function($q) {
                return $q->whereNull('supplier_id');
            })
            ->when(!$isSupplier && $this->supplierIdFilter, function($q) {
                return $q->where('supplier_id', $this->supplierIdFilter);
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->groupBy('status_id')
            ->pluck('total', 'status_id');

        // Consulta de solicitudes con filtros
        $requests = TickRequest::with(['department', 'status', 'creator', 'product'])
            ->when($isSupplier, function($q) use ($user) {
                return $q->where('supplier_id', $user->id);
            })
            ->when(!$isSupplier && $this->type === 'supplier', function($q) {
                return $q->whereNotNull('supplier_id');
            })
            ->when(!$isSupplier && $this->type === 'internal', function($q) {
                return $q->whereNull('supplier_id');
            })
            ->when(!$isSupplier && $this->supplierIdFilter, function($q) {
                return $q->where('supplier_id', $this->supplierIdFilter);
            })
            ->when($this->search, function($q) {
                $q->where('detail', 'like', '%' . $this->search . '%')
                  ->orWhere('id', 'like', '%' . $this->search . '%');
            })
            ->when($this->departmentId, fn($q) => $q->where('department_id', $this->departmentId))
            ->when($this->selectedStatus, fn($q) => $q->where('status_id', $this->selectedStatus))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        // Obtener los proveedores del tenant para el select de filtro del administrador
        $suppliersList = collect();
        if (!$isSupplier) {
            $sessionTenant = session('tenant_id');
            $suppliersList = \App\Models\Auth\User::select('users.id', 'users.name')
                ->join('vnt_contacts', 'users.contact_id', '=', 'vnt_contacts.id')
                ->whereHas('tenants', function ($query) use ($sessionTenant) {
                    $query->where('tenants.id', $sessionTenant);
                })
                ->where('users.profile_id', 17)
                ->where('vnt_contacts.status', 1)
                ->whereNull('vnt_contacts.deleted_at')
                ->distinct()
                ->get();
        }

        $totalQuery = TickRequest::query()
            ->when($isSupplier, function($q) use ($user) {
                return $q->where('supplier_id', $user->id);
            })
            ->when(!$isSupplier && $this->type === 'supplier', function($q) {
                return $q->whereNotNull('supplier_id');
            })
            ->when(!$isSupplier && $this->type === 'internal', function($q) {
                return $q->whereNull('supplier_id');
            })
            ->when(!$isSupplier && $this->supplierIdFilter, function($q) {
                return $q->where('supplier_id', $this->supplierIdFilter);
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo));

        return view('livewire.tenant.tickets.request-list', [
            'requests' => $requests,
            'departments' => TickDepartment::where('status', 1)->get(),
            'statuses' => $statuses,
            'allStats' => $allStats,
            'suppliersList' => $suppliersList,
            'totalRequests' => $totalQuery->count(),
            'isSupplier' => $isSupplier
        ])->layout('layouts.app', [
            'header' => $isSupplier ? 'Requests Panel' : 'Panel de Solicitudes'
        ]);
    }

    public function filterByStatus($statusId = null)
    {
        $this->selectedStatus = $statusId;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'departmentId', 'supplierIdFilter', 'selectedStatus']);
        $this->mount();
        $this->resetPage();
    }
}
