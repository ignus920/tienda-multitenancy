<?php

namespace App\Livewire\Tenant\Tickets;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Tickets\TickDepartment;
use App\Models\Auth\User;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;

class DepartmentManager extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $isModalOpen = false;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    // Campos del formulario
    public $departmentId, $name, $description, $status = 1;

    // Gestión de usuarios
    public $availableUsers = [];
    public $selectedUsers = [];
    public $assignedUsers = []; // IDs de usuarios ya asignados
    public $assignedUsersList = []; // Lista completa de usuarios asignados (para la vista)

    protected $rules = [
        'name' => 'required|min:3',
        'description' => 'nullable|string',
        'status' => 'required|boolean',
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

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $departments = TickDepartment::where('name', 'like', '%' . $this->search . '%')
            ->withCount('users')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.tickets.department-manager', [
            'departments' => $departments
        ])->layout('layouts.app', ['header' => 'Parámetros Departamentos']);
    }

    public function openModal()
    {
        $this->resetForm();
        $this->loadUsers();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->departmentId = null;
        $this->name = '';
        $this->description = '';
        $this->status = 1;
        $this->assignedUsers = [];
        $this->selectedUsers = [];
        $this->availableUsers = [];
    }

    public function loadUsers()
    {
        $sessionTenant = session('tenant_id');
        // Cargamos solo los usuarios vinculados al tenant actual
        $allUsers = User::whereHas('tenants', function ($query) use ($sessionTenant) {
            $query->where('tenants.id', $sessionTenant);
        })->get(['users.id', 'users.name']);

        if ($this->departmentId) {
            $department = TickDepartment::find($this->departmentId);
            $this->assignedUsers = $department->users->pluck('id')->toArray();
        }

        $this->availableUsers = $allUsers->whereNotIn('id', $this->assignedUsers)->toArray();
        $this->assignedUsersList = $allUsers->whereIn('id', $this->assignedUsers)->toArray();
    }

    public function assignUsers()
    {
        if (empty($this->selectedUsers['available'])) return;

        $this->assignedUsers = array_unique(array_merge($this->assignedUsers, $this->selectedUsers['available']));
        $this->selectedUsers['available'] = [];
        $this->updateUserLists();
    }

    public function unassignUsers()
    {
        if (empty($this->selectedUsers['assigned'])) return;

        $this->assignedUsers = array_diff($this->assignedUsers, $this->selectedUsers['assigned']);
        $this->selectedUsers['assigned'] = [];
        $this->updateUserLists();
    }

    private function updateUserLists()
    {
        $sessionTenant = session('tenant_id');
        $allUsers = User::whereHas('tenants', function ($query) use ($sessionTenant) {
            $query->where('tenants.id', $sessionTenant);
        })->get(['users.id', 'users.name']);
        
        $this->availableUsers = $allUsers->whereNotIn('id', $this->assignedUsers)->toArray();
        $this->assignedUsersList = $allUsers->whereIn('id', $this->assignedUsers)->toArray();
    }

    public function toggleStatus($id)
    {
        $this->ensureTenantConnection();
        $department = TickDepartment::findOrFail($id);
        $department->update(['status' => !$department->status]);
        
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Estado actualizado correctamente.'
        ]);
    }

    public function edit($id)
    {
        $this->ensureTenantConnection();
        $department = TickDepartment::with('users')->findOrFail($id);
        
        $this->departmentId = $department->id;
        $this->name = $department->name;
        $this->description = $department->description;
        $this->status = $department->status;
        
        $this->assignedUsers = $department->users->pluck('id')->toArray();
        $this->updateUserLists();
        
        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ];

        DB::beginTransaction();
        try {
            if ($this->departmentId) {
                $department = TickDepartment::find($this->departmentId);
                $department->update($data);
                $msg = 'Departamento actualizado exitosamente.';
            } else {
                $department = TickDepartment::create($data);
                $msg = 'Departamento creado exitosamente.';
            }

            // Sincronizar usuarios (Pivot table)
            $department->users()->sync($this->assignedUsers);

            DB::commit();
            $this->closeModal();
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => $msg
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $this->ensureTenantConnection();
        $department = TickDepartment::findOrFail($id);
        
        if ($department->requests()->exists()) {
            session()->flash('error', 'No se puede eliminar un departamento con solicitudes activas.');
            return;
        }

        $department->delete();
        session()->flash('success', 'Departamento eliminado exitosamente.');
    }
}
