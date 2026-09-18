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
        // No usamos withCount('users'): esa relación cruza central/tenant
        // (User vive en central, tick_department_user vive en tenant) y
        // falla o da resultados inconsistentes en producción. Contamos
        // directo sobre la tabla pivote, que vive en la misma conexión
        // tenant que tick_departments — sin cruce de bases de datos.
        $departments = TickDepartment::where('name', 'like', '%' . $this->search . '%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Contar solo las filas cuyo usuario sigue siendo válido HOY para
        // esta empresa (mismo criterio que Usuarios Disponibles/Asignados:
        // de este tenant, y que no sea Proveedor/Cliente) — si no, filas
        // viejas de usuarios borrados o de otra empresa inflan el número
        // aunque no aparezcan en la lista.
        $sessionTenant = session('tenant_id');
        $validUserIds = User::whereHas('tenants', function ($q) use ($sessionTenant) {
            $q->where('tenants.id', $sessionTenant);
        })->whereNotIn('profile_id', [17, 18])->pluck('id');

        $userCounts = DB::connection('tenant')->table('tick_department_user')
            ->selectRaw('department_id, count(*) as total')
            ->whereIn('department_id', $departments->pluck('id'))
            ->whereIn('user_id', $validUserIds)
            ->groupBy('department_id')
            ->pluck('total', 'department_id');

        foreach ($departments as $department) {
            $department->users_count = $userCounts[$department->id] ?? 0;
        }

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
        if ($this->departmentId) {
            $this->assignedUsers = $this->departmentUserIds($this->departmentId);
        }

        $this->updateUserLists();
    }

    /**
     * IDs de usuarios asignados a un departamento. Consulta directa en la
     * conexión tenant — evitar el JOIN cruzado central/tenant de
     * TickDepartment::users() (User vive en central, tick_department_user
     * vive en tenant), que falla o da resultados vacíos en producción.
     */
    private function departmentUserIds(int $departmentId): array
    {
        return DB::connection('tenant')->table('tick_department_user')
            ->where('department_id', $departmentId)
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
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
        // NO sean proveedores (perfil 17) ni clientes (perfil 18)
        $allUsers = User::whereHas('tenants', function ($query) use ($sessionTenant) {
            $query->where('tenants.id', $sessionTenant);
        })
        ->whereNotIn('profile_id', [17, 18])
        ->get(['users.id', 'users.name']);

        // Podar cualquier id que ya no sea válido hoy (usuario borrado, de
        // otra empresa, o que ahora es Proveedor/Cliente) — así lo que se
        // guarda al final coincide con lo que se ve en pantalla, y de paso
        // limpia filas viejas de la tabla pivote la próxima vez que se
        // guarde este departamento.
        $this->assignedUsers = $allUsers->pluck('id')->intersect($this->assignedUsers)->values()->all();

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
        $department = TickDepartment::findOrFail($id);

        $this->departmentId = $department->id;
        $this->name = $department->name;
        $this->description = $department->description;
        $this->status = $department->status;

        $this->assignedUsers = $this->departmentUserIds($department->id);
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

        DB::connection('tenant')->beginTransaction();
        try {
            if ($this->departmentId) {
                $department = TickDepartment::find($this->departmentId);
                $department->update($data);
                $msg = 'Departamento actualizado exitosamente.';
            } else {
                $department = TickDepartment::create($data);
                $msg = 'Departamento creado exitosamente.';
            }

            // Sincronizar usuarios (tabla pivote) — directo en la conexión
            // tenant, sin pasar por TickDepartment::users()->sync() (esa
            // relación cruza central/tenant y falla en producción).
            $assignedUserIds = array_map('intval', $this->assignedUsers);
            $now = now();

            DB::connection('tenant')->table('tick_department_user')
                ->where('department_id', $department->id)
                ->whereNotIn('user_id', $assignedUserIds)
                ->delete();

            $existingUserIds = $this->departmentUserIds($department->id);
            $newUserIds = array_diff($assignedUserIds, $existingUserIds);

            if (!empty($newUserIds)) {
                DB::connection('tenant')->table('tick_department_user')->insert(
                    array_map(fn ($userId) => [
                        'department_id' => $department->id,
                        'user_id' => $userId,
                        'status' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], $newUserIds)
                );
            }

            DB::connection('tenant')->commit();
            $this->closeModal();
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => $msg
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
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
