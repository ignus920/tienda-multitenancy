<?php

namespace App\Livewire\Tenant\Instructivos;

use App\Helpers\PermissionHelper;
use App\Models\Auth\User;
use App\Models\Tenant\Instructivos\Instructivo;
use App\Models\Tenant\Instructivos\InstructivoDepartment;
use App\Models\Tenant\Instructivos\InstructivoDepartmentUser;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DepartmentShow extends Component
{
    public int $departmentId;

    public bool $canManage = false;

    public bool $showNewModal = false;
    public string $newTitle = '';

    public bool $showMembersModal = false;
    public array $memberUserIds = [];

    public function boot()
    {
        abort_unless(
            PermissionHelper::isSuperAdmin() || PermissionHelper::userCan('Instructivos', 'show'),
            403
        );

        $this->canManage = PermissionHelper::isSuperAdmin() || PermissionHelper::userCan('Instructivos', 'edit');
    }

    public function mount(int $department)
    {
        $exists = InstructivoDepartment::where('status', 1)->where('id', $department)->exists();
        abort_unless($exists, 404);

        $this->departmentId = $department;
    }

    public function render()
    {
        $department = InstructivoDepartment::findOrFail($this->departmentId);

        $instructivos = Instructivo::where('department_id', $this->departmentId)
            ->where('status', 1)
            ->with('author')
            ->withCount(['entries' => fn ($q) => $q->where('status', 1)])
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.tenant.instructivos.department-show', [
            'department' => $department,
            'instructivos' => $instructivos,
        ])->layout('layouts.app', ['header' => 'Instructivos - ' . $department->name]);
    }

    public function openNew()
    {
        abort_unless($this->canManage, 403);
        $this->newTitle = '';
        $this->showNewModal = true;
    }

    public function saveNew()
    {
        abort_unless($this->canManage, 403);

        $this->validate([
            'newTitle' => 'required|string|max:150',
        ]);

        $instructivo = Instructivo::create([
            'department_id' => $this->departmentId,
            'title' => $this->newTitle,
            'status' => 1,
            'created_by' => Auth::id(),
        ]);

        $this->showNewModal = false;

        return redirect()->route('tenant.instructivos.show', $instructivo->id);
    }

    public function deactivateInstructivo(int $id)
    {
        abort_unless($this->canManage, 403);
        Instructivo::where('id', $id)->where('department_id', $this->departmentId)->update(['status' => 0]);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Instructivo desactivado.']);
    }

    public function openMembers()
    {
        abort_unless($this->canManage, 403);
        $this->memberUserIds = InstructivoDepartmentUser::where('department_id', $this->departmentId)
            ->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $this->showMembersModal = true;
    }

    public function saveMembers()
    {
        abort_unless($this->canManage, 403);

        $current = InstructivoDepartmentUser::where('department_id', $this->departmentId)->pluck('user_id')->all();
        $selected = array_map('intval', $this->memberUserIds);

        $toRemove = array_diff($current, $selected);
        $toAdd = array_diff($selected, $current);

        if (!empty($toRemove)) {
            InstructivoDepartmentUser::where('department_id', $this->departmentId)
                ->whereIn('user_id', $toRemove)->delete();
        }

        foreach ($toAdd as $userId) {
            InstructivoDepartmentUser::create([
                'department_id' => $this->departmentId,
                'user_id' => $userId,
            ]);
        }

        $this->showMembersModal = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Miembros actualizados.']);
    }

    public function getTenantUsersProperty()
    {
        $tenantId = session('tenant_id');
        return User::where('tenant_id', $tenantId)->orderBy('name')->get(['id', 'name']);
    }
}
