<?php

namespace App\Livewire\Tenant\Movements\Components;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Movements\InvInventoryAdjustment;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

class MovementList extends Component
{
    use WithPagination;

    public $type = 'entrada'; // 'entrada' or 'salida'
    public $search = '';
    public $perPage = 10;
    public $sortField = 'date';
    public $sortDirection = 'desc';

    protected $listeners = ['refreshMovements' => 'refreshList'];

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
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function refreshList($type = null)
    {
        // If a type is provided and it matches current type, refresh the list
        if ($type && $type === $this->type) {
            $this->resetPage();
        }
        $this->dispatch('$refresh');
    }

    public function getMovementsProperty()
    {
        $this->ensureTenantConnection();
        return InvInventoryAdjustment::query()
            ->byType($this->type)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('consecutive', 'like', '%' . $this->search . '%')
                        ->orWhere('observations', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.tenant.movements.components.movement-list', [
            'movements' => $this->movements,
        ]);
    }

     private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            session()->forget('tenant_id');
            throw new \Exception('Invalid tenant');
        }
        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
