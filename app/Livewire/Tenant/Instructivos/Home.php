<?php

namespace App\Livewire\Tenant\Instructivos;

use App\Helpers\PermissionHelper;
use App\Models\Auth\Tenant;
use App\Models\Tenant\Instructivos\InstructivoDepartment;
use App\Models\Tenant\Instructivos\InstructivoDepartmentUser;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Home extends Component
{
    public bool $canManage = false;

    public bool $showDeptModal = false;
    public ?int $editingDeptId = null;
    public string $deptName = '';
    public string $deptIcon = '📄';
    public string $deptColor = '#6366f1';

    public function boot()
    {
        $this->ensureTenantConnection();

        abort_unless(
            PermissionHelper::isSuperAdmin() || PermissionHelper::userCan('Instructivos', 'show'),
            403
        );

        $this->canManage = PermissionHelper::isSuperAdmin() || PermissionHelper::userCan('Instructivos', 'edit');
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

    public function render()
    {
        $myDeptIds = InstructivoDepartmentUser::where('user_id', Auth::id())->pluck('department_id')->all();

        $departments = InstructivoDepartment::where('status', 1)
            ->orderBy('order')
            ->orderBy('name')
            ->get()
            ->sortByDesc(fn ($d) => in_array($d->id, $myDeptIds))
            ->values();

        return view('livewire.tenant.instructivos.home', [
            'departments' => $departments,
            'myDeptIds' => $myDeptIds,
        ])->layout('layouts.app', ['header' => 'Instructivos']);
    }

    public function openCreateDept()
    {
        abort_unless($this->canManage, 403);
        $this->resetDeptForm();
        $this->showDeptModal = true;
    }

    public function openEditDept(int $id)
    {
        abort_unless($this->canManage, 403);
        $dept = InstructivoDepartment::findOrFail($id);
        $this->editingDeptId = $dept->id;
        $this->deptName = $dept->name;
        $this->deptIcon = $dept->icon ?: '📄';
        $this->deptColor = $dept->color ?: '#6366f1';
        $this->showDeptModal = true;
    }

    public function saveDept()
    {
        abort_unless($this->canManage, 403);

        $this->validate([
            'deptName' => 'required|string|max:100',
            'deptIcon' => 'required|string|max:10',
            'deptColor' => 'required|string|max:20',
        ]);

        InstructivoDepartment::updateOrCreate(
            ['id' => $this->editingDeptId],
            [
                'name' => $this->deptName,
                'icon' => $this->deptIcon,
                'color' => $this->deptColor,
                'status' => 1,
            ]
        );

        $this->showDeptModal = false;
        $this->resetDeptForm();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Departamento guardado.']);
    }

    public function deactivateDept(int $id)
    {
        abort_unless($this->canManage, 403);
        InstructivoDepartment::where('id', $id)->update(['status' => 0]);
        $this->showDeptModal = false;
        $this->resetDeptForm();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Departamento desactivado.']);
    }

    private function resetDeptForm()
    {
        $this->editingDeptId = null;
        $this->deptName = '';
        $this->deptIcon = '📄';
        $this->deptColor = '#6366f1';
        $this->resetErrorBag();
    }
}
