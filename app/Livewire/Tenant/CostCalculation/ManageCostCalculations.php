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
        return (int) $calc->created_by === (int) Auth::id();
    }

    public function updatedSearch()
    {
        $this->resetPage();
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
            ->when($this->search, function ($q) {
                $words = array_filter(explode(' ', trim($this->search)));
                foreach ($words as $word) {
                    $q->where('name', 'like', '%' . $word . '%');
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.tenant.cost-calculation.manage-cost-calculations', [
            'calculations' => $calculations,
        ])->layout('layouts.app', ['header' => 'Cálculo de Costos']);
    }
}
