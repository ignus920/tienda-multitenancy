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
    public $dateFrom;
    public $dateTo;
    public $selectedStatus = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'departmentId' => ['except' => ''],
        'selectedStatus' => ['except' => null],
    ];

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

        // Consultas para los estados (Dashboard)
        $statuses = TickStatus::orderBy('order')->get();
        $allStats = TickRequest::selectRaw('status_id, count(*) as total')
            ->groupBy('status_id')
            ->pluck('total', 'status_id');

        // Consulta de solicitudes con filtros
        $requests = TickRequest::with(['department', 'status', 'creator', 'product'])
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

        return view('livewire.tenant.tickets.request-list', [
            'requests' => $requests,
            'departments' => TickDepartment::where('status', 1)->get(),
            'statuses' => $statuses,
            'allStats' => $allStats,
            'totalRequests' => TickRequest::count()
        ])->layout('layouts.app');
    }

    public function filterByStatus($statusId = null)
    {
        $this->selectedStatus = $statusId;
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'departmentId', 'selectedStatus']);
        $this->mount();
        $this->resetPage();
    }
}
