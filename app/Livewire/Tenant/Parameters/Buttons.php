<?php

namespace App\Livewire\Tenant\Parameters;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Tenant\Parameters\CnfButtons;
use App\Models\Central\VntModul;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Carbon\Carbon;

class Buttons extends Component
{
    use WithPagination;
    use \App\Traits\Livewire\WithExport;

    // Propiedades de búsqueda y paginación
    public $search = '';
    public $sortField = 'tittle';
    public $sortDirection = 'asc';
    public $perPage = 10;

    // Propiedades de control de interfaz
    public $showModal = false;
    public $editingId = null;

    // Propiedades del formulario
    public $tittle = '';
    public $link = '';
    public $color = '';
    public $modul_id = '';
    public $status = 1;

    protected $listeners = [
        'item-selected' => 'handleItemSelected'
    ];

    public function handleItemSelected($value)
    {
        $this->modul_id = $value;
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

    protected function rules()
    {
        return [
            'tittle' => 'required|string|max:200',
            'link' => 'required|string|max:255',
            'color' => 'required|string',
            'modul_id' => 'required',
            'status' => 'required|boolean',
        ];
    }

    protected function messages()
    {
        return [
            'tittle.required' => 'El título es obligatorio.',
            'tittle.max' => 'El título no puede superar los 200 caracteres.',
            'link.required' => 'El vínculo es obligatorio.',
            'link.max' => 'El vínculo no puede superar los 255 caracteres.',
            'color.required' => 'El color es obligatorio.',
            'modul_id.required' => 'El módulo es obligatorio.',
            'status.required' => 'El estado es obligatorio.',
        ];
    }

    #[Computed]
    public function modulus()
    {
        return VntModul::where('status', 1)->get()
            ->map(function ($module) {
                return [
                    'id' => $module->id,
                    'firstName' => $module->name
                ];
            })->toArray();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->ensureTenantConnection();
        $item = CnfButtons::findOrFail($id);

        $this->editingId = $item->id;
        $this->tittle = $item->tittle;
        $this->link = $item->link;
        $this->color = $item->color;
        $this->modul_id = $item->module;
        $this->status = $item->status ? 1 : 0;

        $this->showModal = true;
    }

    public function toggleItemStatus($id)
    {
        try {
            $this->ensureTenantConnection();
            $item = CnfButtons::findOrFail($id);
            $item->status = !$item->status;
            $item->save();

            session()->flash('message', 'Estado actualizado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar estado: ' . $e->getMessage());
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $this->ensureTenantConnection();
            $data = [
                'tittle' => $this->tittle,
                'link' => $this->link,
                'color' => $this->color,
                'module' => $this->modul_id,
                'status' => $this->status,
            ];

            if ($this->editingId) {
                $item = CnfButtons::findOrFail($this->editingId);
                $item->update($data);
                session()->flash('message', 'Registro actualizado exitosamente.');
            } else {
                CnfButtons::create($data);
                session()->flash('message', 'Registro creado exitosamente.');
            }

            $this->showModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->tittle = '';
        $this->link = '';
        $this->color = '';
        $this->modul_id = '';
        $this->status = 1;
        $this->resetErrorBag();
    }

    public function cancel()
    {
        $this->resetValidation();
        $this->reset([
            'tittle',
            'link',
            'color',
            'modul_id',
            'status',
        ]);
        $this->showModal = false;
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $items = CnfButtons::query()
            ->where(function ($query) {
                $query->where('tittle', 'like', '%' . $this->search . '%')
                    ->orWhere('link', 'like', '%' . $this->search . '%')
                    ->orWhere('color', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.parameters.buttons', [
            'items' => $items
        ])->layout('layouts.app', ['header' => 'Gestión de Botones']);
    }

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

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
