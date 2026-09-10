<?php

namespace App\Livewire\Tenant\Parameters;

use App\Helpers\PermissionHelper;
use App\Models\Central\UsrPermission;
use App\Models\Central\UsrProfile;
use App\Services\PermissionCatalogService;
use Livewire\Component;

class ProfilePermissionManager extends Component
{
    /** Acciones de la matriz (columna => etiqueta) */
    public array $actions = [
        'show'   => 'Ver',
        'create' => 'Crear',
        'edit'   => 'Editar',
        'delete' => 'Desactivar',
    ];

    public $selectedProfileId = null;

    /**
     * matriz: [ permissionId => ['id'=>, 'name'=>, 'show'=>bool, 'create'=>bool, 'edit'=>bool, 'delete'=>bool] ]
     */
    public array $matrix = [];

    public function mount(): void
    {
        abort_unless(PermissionHelper::userCan('Usuarios', 'edit') || PermissionHelper::isSuperAdmin(), 403);
    }

    public function updatedSelectedProfileId($value): void
    {
        $this->loadMatrix($value ? (int) $value : null);
    }

    private function loadMatrix(?int $profileId): void
    {
        $this->matrix = [];

        if (!$profileId) {
            return;
        }

        $permissions = UsrPermission::where('status', 1)->orderBy('name')->get();

        $assigned = [];
        $profile = UsrProfile::with(['permissions' => fn ($q) => $q->where('status', 1)])->find($profileId);
        if ($profile) {
            foreach ($profile->permissions as $p) {
                $assigned[$p->id] = [
                    'show'   => (bool) $p->pivot->show,
                    'create' => (bool) $p->pivot->creater,
                    'edit'   => (bool) $p->pivot->editer,
                    'delete' => (bool) $p->pivot->deleter,
                ];
            }
        }

        foreach ($permissions as $perm) {
            $flags = $assigned[$perm->id] ?? ['show' => false, 'create' => false, 'edit' => false, 'delete' => false];
            $this->matrix[$perm->id] = array_merge(
                ['id' => $perm->id, 'name' => $perm->name],
                $flags
            );
        }
    }

    /** Marca o desmarca una columna entera. */
    public function toggleColumn(string $action): void
    {
        if (!array_key_exists($action, $this->actions)) {
            return;
        }

        // Si TODOS están marcados -> desmarcar todo; si no -> marcar todo.
        $allChecked = collect($this->matrix)->every(fn ($row) => !empty($row[$action]));
        foreach ($this->matrix as $id => $row) {
            $this->matrix[$id][$action] = !$allChecked;
        }
    }

    public function save(): void
    {
        abort_unless(PermissionHelper::userCan('Usuarios', 'edit') || PermissionHelper::isSuperAdmin(), 403);

        if (!$this->selectedProfileId) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Selecciona un perfil.']);
            return;
        }

        $rows = [];
        foreach ($this->matrix as $row) {
            $rows[] = [
                'permissionId' => $row['id'],
                'show'   => !empty($row['show']),
                'create' => !empty($row['create']),
                'edit'   => !empty($row['edit']),
                'delete' => !empty($row['delete']),
            ];
        }

        $result = app(PermissionCatalogService::class)
            ->upsertProfilePermissions((int) $this->selectedProfileId, $rows);

        PermissionHelper::clearCache();

        $this->dispatch('show-toast', [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ]);
    }

    public function render()
    {
        $profiles = UsrProfile::where('status', 1)->orderBy('name')->get(['id', 'name']);

        return view('livewire.tenant.parameters.profile-permission-manager', [
            'profiles' => $profiles,
        ])->layout('layouts.app', ['header' => 'Permisos por Perfil']);
    }
}
