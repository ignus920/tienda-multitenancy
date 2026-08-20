<?php

namespace App\Livewire\Tenant\Warranties;

use App\Models\Tenant\Sales\VntWarranty;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

class WarrantiesList extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom;
    public $dateTo;
    public $filterStatus = null;
    public $perPage = 10;

    // Contadores
    public $countPending = 0;
    public $countLab = 0;
    public $countImports = 0;
    public $countResolved = 0;

    protected $listeners = [
        'refreshWarranties' => '$refresh',
        'loadCounts' => 'loadCounts'
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => null],
        'perPage' => ['except' => 10],
    ];

    public function boot()
    {
        $this->ensureTenantConnection();
    }

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
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        $this->loadCounts();
    }

    public function loadCounts()
    {
        $counts = VntWarranty::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $this->countPending = $counts->where('status', 1)->sum('total');
        $this->countLab = $counts->where('status', 2)->sum('total');
        $this->countImports = $counts->where('status', 3)->sum('total');
        $this->countResolved = $counts->where('status', 4)->sum('total');
    }

    public function applyFilter($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function updatedSearch()
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

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterStatus']);
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function render()
    {
        $warranties = VntWarranty::with(['remission.quote.customer', 'items.item', 'user'])
            ->when($this->search, function ($query) {
                $query->where('consecutive', 'like', '%' . $this->search . '%')
                    ->orWhereHas('remission', function ($q) {
                        $q->where('consecutive', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('remission.quote.customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('items.item', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            }, function ($query) {
                $query->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        $this->loadCounts();

        return view('livewire.tenant.warranties.warranties-list', [
            'warranties' => $warranties
        ])->layout('layouts.app');
    }
}
