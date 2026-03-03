<?php

namespace App\Livewire\Tenant\Production;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Production\PrdProcess;
use App\Models\Tenant\Production\PrdFieldsProcess;
use App\Models\Tenant\Production\PrdUsersProcess;
use App\Models\Auth\User;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

class ManageProcess extends Component
{
    use WithPagination;

    // Búsqueda / paginación
    public $search = '';
    public $perPage = 10;

    // ── Modal Proceso ────────────────────────────────────
    public $showModal = false;
    public $editingId = null;

    public $name = '';
    public $previous_notes = '';
    public $inventory_consumption = 0;
    public $documents_generates = 0;
    public $status = 1;

    // Operarios asignados al proceso
    public array $selectedOperators = [];   // IDs seleccionados
    public array $operatorUsers = [];       // Lista de usuarios perfil 8

    // ── Modal Campos ─────────────────────────────────────
    public $showFieldsModal = false;
    public $selectedProcessId = null;
    public $selectedProcessName = '';
    public $processFields = [];

    public $editingFieldId = null;
    public $fieldName = '';
    public $fieldLabel = '';
    public $fieldType = 'text';
    public $fieldClass = '';
    public $fieldOptions = '';
    public $fieldStatus = 1;

    public $fieldTypes = [
        'text'      => 'Texto (input)',
        'text_area' => 'Área de texto',
        'select'    => 'Selección (select)',
        'number'    => 'Número',
        'date'      => 'Fecha',
        'checkbox'  => 'Casilla (checkbox)',
    ];

    // ── Validación Proceso ────────────────────────────────
    protected function rulesProcess(): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'previous_notes'        => 'nullable|string',
            'inventory_consumption' => 'required|boolean',
            'documents_generates'   => 'required|boolean',
            'status'                => 'required|boolean',
        ];
    }

    // ── Validación Campo ──────────────────────────────────
    protected function rulesField(): array
    {
        return [
            'fieldName'    => 'required|string|max:100|regex:/^[a-z0-9_]+$/',
            'fieldLabel'   => 'required|string|max:255',
            'fieldType'    => 'required|in:text,text_area,select,number,date,checkbox',
            'fieldClass'   => 'nullable|string|max:255',
            'fieldOptions' => 'nullable|string',
            'fieldStatus'  => 'required|boolean',
        ];
    }

    protected $messages = [
        'name.required'       => 'El nombre del proceso es obligatorio.',
        'fieldName.required'  => 'El nombre interno es obligatorio.',
        'fieldName.regex'     => 'Solo letras minúsculas, números y guión bajo (ej: tamaño_papel).',
        'fieldLabel.required' => 'La etiqueta del campo es obligatoria.',
        'fieldType.required'  => 'El tipo de campo es obligatorio.',
        'fieldType.in'        => 'Tipo de campo no válido.',
    ];

    protected $queryString = [
        'search'  => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount()
    {
        $this->ensureTenantConnection();
        $this->loadOperatorUsers();
    }

    private function loadOperatorUsers()
    {
        $this->operatorUsers = User::on('central')
            ->where('profile_id', 8)
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->toArray();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ── CRUD Procesos ─────────────────────────────────────

    public function create()
    {
        $this->resetProcessForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->ensureTenantConnection();
        $process = PrdProcess::findOrFail($id);
        $this->editingId             = $process->id;
        $this->name                  = $process->name;
        $this->previous_notes        = $process->previous_notes ?? '';
        $this->inventory_consumption = $process->inventory_consumption;
        $this->documents_generates   = $process->documents_generates;
        $this->status                = $process->status;

        // Cargar operarios ya asignados
        $this->selectedOperators = PrdUsersProcess::where('process_id', $id)
            ->whereNull('deleted_at')
            ->pluck('operator_user_id')
            ->map(fn($v) => (string) $v)
            ->toArray();

        $this->showModal = true;
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->validate($this->rulesProcess());

        $data = [
            'name'                  => $this->name,
            'previous_notes'        => $this->previous_notes ?: null,
            'inventory_consumption' => (int) $this->inventory_consumption,
            'documents_generates'   => (int) $this->documents_generates,
            'status'                => (int) $this->status,
        ];

        if ($this->editingId) {
            PrdProcess::findOrFail($this->editingId)->update($data);
            $processId = $this->editingId;
            session()->flash('success', 'Proceso actualizado correctamente.');
        } else {
            $process   = PrdProcess::create($data);
            $processId = $process->id;
            session()->flash('success', 'Proceso creado correctamente.');
        }

        // Sincronizar operarios asignados
        $this->syncOperators($processId);

        $this->resetProcessForm();
        $this->showModal = false;
    }

    private function syncOperators(int $processId): void
    {
        // Soft-delete los que ya no están seleccionados
        PrdUsersProcess::where('process_id', $processId)
            ->whereNull('deleted_at')
            ->whereNotIn('operator_user_id', $this->selectedOperators)
            ->delete();

        // Insertar o restaurar los nuevos seleccionados
        foreach ($this->selectedOperators as $userId) {
            PrdUsersProcess::withTrashed()
                ->updateOrCreate(
                    ['process_id' => $processId, 'operator_user_id' => $userId],
                    ['status' => 1, 'deleted_at' => null]
                );
        }
    }

    public function delete($id)
    {
        $this->ensureTenantConnection();
        PrdProcess::findOrFail($id)->delete();
        session()->flash('success', 'Proceso eliminado correctamente.');
    }

    public function toggleStatus($id)
    {
        $this->ensureTenantConnection();
        $process = PrdProcess::findOrFail($id);
        $process->update(['status' => $process->status ? 0 : 1]);
    }

    // ── Campos del proceso ────────────────────────────────

    public function openFields($processId)
    {
        $this->ensureTenantConnection();
        $process = PrdProcess::findOrFail($processId);
        $this->selectedProcessId   = $processId;
        $this->selectedProcessName = $process->name;
        $this->resetFieldForm();
        $this->loadFields();
        $this->showFieldsModal = true;
    }

    private function loadFields()
    {
        $this->processFields = PrdFieldsProcess::where('processId', $this->selectedProcessId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    public function updatedFieldLabel($value)
    {
        // Auto-genera el nombre interno solo cuando se crea un campo nuevo
        if (!$this->editingFieldId) {
            $this->fieldName = \Illuminate\Support\Str::slug($value, '_');
        }
    }

    public function editField($fieldId)
    {
        $this->ensureTenantConnection();
        $field = PrdFieldsProcess::findOrFail($fieldId);
        $this->editingFieldId = $field->id;
        $this->fieldName      = $field->name;
        $this->fieldLabel     = $field->label;
        $this->fieldType      = $field->type;
        $this->fieldClass     = $field->class ?? '';
        $this->fieldOptions   = $field->options ?? '';
        $this->fieldStatus    = $field->status;
    }

    public function saveField()
    {
        $this->ensureTenantConnection();
        $this->validate($this->rulesField());

        $data = [
            'processId' => $this->selectedProcessId,
            'name'      => $this->fieldName,
            'label'     => $this->fieldLabel,
            'type'      => $this->fieldType,
            'class'     => $this->fieldClass ?: null,
            'options'   => in_array($this->fieldType, ['select']) ? ($this->fieldOptions ?: null) : null,
            'status'    => (int) $this->fieldStatus,
        ];

        if ($this->editingFieldId) {
            PrdFieldsProcess::findOrFail($this->editingFieldId)->update($data);
        } else {
            PrdFieldsProcess::create($data);
        }

        $this->resetFieldForm();
        $this->loadFields();
    }

    public function deleteField($fieldId)
    {
        $this->ensureTenantConnection();
        PrdFieldsProcess::findOrFail($fieldId)->delete();
        $this->loadFields();
    }

    public function toggleFieldStatus($fieldId)
    {
        $this->ensureTenantConnection();
        $field = PrdFieldsProcess::findOrFail($fieldId);
        $field->update(['status' => $field->status ? 0 : 1]);
        $this->loadFields();
    }

    public function cancelFieldEdit()
    {
        $this->resetFieldForm();
    }

    // ── Resets ────────────────────────────────────────────

    private function resetProcessForm()
    {
        $this->editingId             = null;
        $this->name                  = '';
        $this->previous_notes        = '';
        $this->inventory_consumption = 0;
        $this->documents_generates   = 0;
        $this->status                = 1;
        $this->selectedOperators     = [];
        $this->resetErrorBag();
    }

    private function resetFieldForm()
    {
        $this->editingFieldId = null;
        $this->fieldName      = '';
        $this->fieldLabel     = '';
        $this->fieldType      = 'text';
        $this->fieldClass     = '';
        $this->fieldOptions   = '';
        $this->fieldStatus    = 1;
        $this->resetErrorBag();
    }

    // ── Tenant connection ─────────────────────────────────

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $processes = PrdProcess::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('previous_notes', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.tenant.production.manage-process', [
            'processes' => $processes,
        ])->layout('layouts.app');
    }
}
