<?php

namespace App\Livewire\Tenant\CostCalculation;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\CostCalculation\CostCalculation;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;

class ManageCostCalculations extends Component
{
    use WithPagination;

    const FULL_ACCESS_PROFILES = [1, 2];

    public $search = '';
    public $filterType = 'project';
    public $dateFrom;
    public $dateTo;

    public function mount()
    {
        $this->dateFrom = now()->subMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

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

        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }

        config(['database.connections.tenant.database' => $tenant->tenancy_db_name]);
    }

    public function isGerencia(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        if (in_array((int) $user->profile_id, self::FULL_ACCESS_PROFILES, true)) return true;
        return str_contains(strtolower($user->profile->name ?? ''), 'gerencia');
    }

    public function canEditCalc($calc): bool
    {
        if ($this->isGerencia()) return true;
        if ($calc->type === 'finished_product') return false;
        return (int) $calc->created_by === (int) Auth::id();
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

    public function clearFilters()
    {
        $this->search = '';
        $this->dateFrom = now()->subMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
    }

    public function markAsFinishedProduct($id)
    {
        if (!$this->isGerencia()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No tienes permisos para realizar esta acción.']);
            return;
        }

        $this->ensureTenantConnection();
        $calc = CostCalculation::find($id);
        if ($calc) {
            $calc->update(['type' => 'finished_product']);
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Marcado como producto terminado.']);
        }
    }

    public function openInstructivo()
    {
        $this->ensureTenantConnection();

        $dept = \App\Models\Tenant\Instructivos\InstructivoDepartment::firstOrCreate(
            ['name' => 'Módulos del Sistema'],
            [
                'status' => 1,
                'is_private' => 1,
                'order' => 99,
                'color' => '#4F46E5',
                'icon' => 'cog'
            ]
        );

        $instructivo = \App\Models\Tenant\Instructivos\Instructivo::firstOrCreate(
            ['department_id' => $dept->id, 'title' => 'Cálculo de Costos'],
            [
                'status' => 1,
                'created_by' => \Illuminate\Support\Facades\Auth::id()
            ]
        );

        return redirect()->route('tenant.instructivos.show', $instructivo->id);
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $calculations = CostCalculation::with(['creator', 'items'])
            ->where('type', $this->filterType)
            ->when($this->search, function ($q) {
                $words = array_filter(explode(' ', trim($this->search)));
                foreach ($words as $word) {
                    $q->where(function ($sub) use ($word) {
                        $sub->where('name', 'like', '%' . $word . '%')
                            ->orWhereHas('items', function ($itemQ) use ($word) {
                                $itemQ->where('description', 'like', '%' . $word . '%');
                            });
                    });
                }
            }, function ($q) {
                if ($this->dateFrom && $this->dateTo) {
                    $q->whereBetween('created_at', [$this->dateFrom . ' 00:00:00', $this->dateTo . ' 23:59:59']);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.tenant.cost-calculation.manage-cost-calculations', [
            'calculations' => $calculations,
        ])->layout('layouts.app', ['header' => 'Cálculo de Costos']);
    }
}
