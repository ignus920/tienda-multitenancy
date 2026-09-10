<?php

namespace App\Livewire\Tenant\Parameters;

use App\Helpers\PermissionHelper;
use App\Models\Central\UsrPermission;
use App\Models\Central\UsrProfile;
use App\Services\PermissionCatalogService;
use Illuminate\Support\Str;
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
     * Estructura para la vista (agrupada por "grupo" del catálogo):
     * [ ['key'=>slug, 'title'=>str, 'single'=>bool, 'perms'=>[ ['id'=>, 'label'=>, 'name'=>] ] ] ]
     */
    public array $groups = [];

    /** matriz plana para wire:model: [ permissionId => ['show'=>bool,'create'=>bool,'edit'=>bool,'delete'=>bool] ] */
    public array $matrix = [];

    public function mount(): void
    {
        abort_unless(PermissionHelper::isSuperAdmin(), 403);
    }

    public function updatedSelectedProfileId($value): void
    {
        $this->loadMatrix($value ? (int) $value : null);
    }

    private function loadMatrix(?int $profileId): void
    {
        $this->groups = [];
        $this->matrix = [];

        if (!$profileId) {
            return;
        }

        $permissions = UsrPermission::where('status', 1)
            ->orderByRaw('COALESCE(grupo, name)')
            ->orderBy('label')
            ->orderBy('name')
            ->get();

        // Flags ya asignados al perfil
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

        $buckets = []; // grupo => [perms]
        $singles = [];

        foreach ($permissions as $perm) {
            $this->matrix[$perm->id] = $assigned[$perm->id]
                ?? ['show' => false, 'create' => false, 'edit' => false, 'delete' => false];

            $entry = [
                'id'    => $perm->id,
                'name'  => $perm->name,
                'label' => $perm->label ?: $perm->name,
            ];

            if ($perm->grupo) {
                $buckets[$perm->grupo][] = $entry;
            } else {
                $singles[] = $entry;
            }
        }

        // Grupos con subsecciones
        foreach ($buckets as $grupo => $perms) {
            $this->groups[] = [
                'key'    => Str::slug($grupo),
                'title'  => $grupo,
                'single' => false,
                'perms'  => $perms,
            ];
        }

        // Módulos sueltos (sin grupo) -> una fila cada uno
        foreach ($singles as $entry) {
            $this->groups[] = [
                'key'    => 'x-' . $entry['id'],
                'title'  => $entry['label'],
                'single' => true,
                'perms'  => [$entry],
            ];
        }

        // Orden alfabético por título
        usort($this->groups, fn ($a, $b) => strcasecmp($a['title'], $b['title']));
    }

    /** Marca/desmarca una acción para TODAS las subsecciones de un grupo (ids explícitos). */
    public function toggleGroupColumn(array $ids, string $action): void
    {
        if (!array_key_exists($action, $this->actions) || !$ids) {
            return;
        }

        $ids = array_map('intval', $ids);
        $allChecked = collect($ids)->every(fn ($id) => !empty($this->matrix[$id][$action] ?? false));

        foreach ($ids as $id) {
            if (isset($this->matrix[$id])) {
                $this->matrix[$id][$action] = !$allChecked;
            }
        }
    }

    public function save(): void
    {
        abort_unless(PermissionHelper::isSuperAdmin(), 403);

        if (!$this->selectedProfileId) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Selecciona un perfil.']);
            return;
        }

        $rows = [];
        foreach ($this->matrix as $id => $flags) {
            $rows[] = [
                'permissionId' => (int) $id,
                'show'   => !empty($flags['show']),
                'create' => !empty($flags['create']),
                'edit'   => !empty($flags['edit']),
                'delete' => !empty($flags['delete']),
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
