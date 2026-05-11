<?php

namespace App\Livewire\Tenant\Returns;

use App\Models\Tenant\Sales\VntReturn;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

class ReturnsList extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom;
    public $dateTo;
    public $filterStatus = null;

    // Contadores
    public $countComercial = 0;
    public $countLab = 0;
    public $countAccounting = 0;

    protected $listeners = [
        'refreshReturns' => '$refresh',
        'loadCounts' => 'loadCounts'
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => null],
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
        $counts = VntReturn::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $this->countComercial = $counts->whereIn('status', [1, 6])->sum('total');
        $this->countLab = $counts->where('status', 2)->sum('total');
        $this->countAccounting = $counts->where('status', 3)->sum('total');
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

    public function render()
    {
        $returns = VntReturn::with(['remission.quote.customer', 'item', 'user'])
            ->when($this->search, function ($query) {
                $query->whereHas('remission', function ($q) {
                    $q->where('id', 'like', '%' . $this->search . '%')
                      ->orWhere('consecutive', 'like', '%' . $this->search . '%');
                })->orWhereHas('item', function ($q) {
                    $q->where('descripcion', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function ($query) {
                if ($this->filterStatus === 'comercial') {
                    $query->whereIn('status', [1, 6]);
                } elseif ($this->filterStatus === 'laboratorio') {
                    $query->where('status', 2);
                } elseif ($this->filterStatus === 'contabilidad') {
                    $query->where('status', 3);
                }
            }, function ($query) {
                // Si no hay filtro de estado, aplicar filtro de fecha
                $query->whereBetween('requested_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.tenant.returns.returns-list', [
            'returns' => $returns
        ])->layout('layouts.app');
    }
}
