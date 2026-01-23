<?php

namespace App\Livewire\Tenant\Warehouses;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Customer\VntWarehouse;
use App\Models\Tenant\Customer\VntCompany;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;

class WarehouseManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editingId = null;
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $successMessage = '';
    public $errorMessage = '';

    // Propiedades del formulario
    public $companyId = '';
    public $name = '';
    public $address = '';
    public $cityId = '';
    public $postcode = '';
    public $branch_type = 'FIJA';
    public $status = true;

    protected $rules = [
        'companyId' => 'required|exists:vnt_companies,id',
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:500',
        'cityId' => 'required|exists:cities,id',
        'postcode' => 'nullable|string|max:20',
        'branch_type' => 'required|in:FIJA,DESPACHO',
        'status' => 'boolean',
    ];

    protected $messages = [
        'companyId.required' => 'La empresa es obligatoria',
        'companyId.exists' => 'La empresa seleccionada no es válida',
        'name.required' => 'El nombre es obligatorio',
        'name.max' => 'El nombre no puede exceder 255 caracteres',
        'address.required' => 'La dirección es obligatoria',
        'address.max' => 'La dirección no puede exceder 500 caracteres',
        'cityId.required' => 'La ciudad es obligatoria',
        'cityId.exists' => 'La ciudad seleccionada no es válida',
        'postcode.max' => 'El código postal no puede exceder 20 caracteres',
        'branch_type.required' => 'El tipo de sucursal es obligatorio',
        'branch_type.in' => 'El tipo de sucursal debe ser FIJA o DESPACHO',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'city-changed' => 'updateCity'
    ];

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
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }

    public function mount()
    {
        $this->ensureTenantConnection();
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

    public function render()
    {
        $this->ensureTenantConnection();

        $warehouses = VntWarehouse::query()
            ->with(['company', 'city'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%')
                      ->orWhereHas('company', function ($q) {
                          $q->where('businessName', 'like', '%' . $this->search . '%')
                            ->orWhere('firstName', 'like', '%' . $this->search . '%')
                            ->orWhere('lastName', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('city', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $companies = VntCompany::orderBy('businessName')->get();

        return view('livewire.tenant.warehouses.warehouse-manager', [
            'warehouses' => $warehouses,
            'companies' => $companies
        ])->layout('layouts.app');
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->ensureTenantConnection();

        $warehouse = VntWarehouse::findOrFail($id);
        $this->editingId = $id;
        $this->companyId = $warehouse->companyId;
        $this->name = $warehouse->name;
        $this->address = $warehouse->address;
        $this->cityId = $warehouse->cityId;
        $this->postcode = $warehouse->postcode;
        $this->branch_type = $warehouse->branch_type ?? 'FIJA';
        $this->status = $warehouse->status;
        $this->showModal = true;

        $this->clearMessages();
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->validate();

        try {
            $data = [
                'companyId' => $this->companyId,
                'name' => $this->name,
                'address' => $this->address,
                'cityId' => $this->cityId,
                'postcode' => $this->postcode ?: null,
                'branch_type' => $this->branch_type,
                'status' => $this->status ? 1 : 0,
                'main' => 0 // Las sucursales creadas aquí no son principales
            ];

            if ($this->editingId) {
                $warehouse = VntWarehouse::findOrFail($this->editingId);

                // No permitir editar sucursal principal
                if ($warehouse->main) {
                    $this->errorMessage = 'No se puede editar la sucursal principal desde este módulo';
                    return;
                }

                $warehouse->update($data);
                $this->successMessage = 'Sucursal actualizada exitosamente.';
            } else {
                VntWarehouse::create($data);
                $this->successMessage = 'Sucursal creada exitosamente.';
            }

            $this->resetForm();
            $this->showModal = false;

        } catch (\Exception $e) {
            Log::error('Error saving warehouse', [
                'mode' => $this->editingId ? 'edit' : 'create',
                'error' => $e->getMessage()
            ]);

            $this->errorMessage = 'Error al guardar la sucursal: ' . $e->getMessage();
        }
    }

    public function delete($id)
    {
        try {
            $this->ensureTenantConnection();

            $warehouse = VntWarehouse::findOrFail($id);

            // No permitir eliminar sucursal principal
            if ($warehouse->main) {
                $this->errorMessage = 'No se puede eliminar la sucursal principal';
                return;
            }

            $warehouse->delete();
            $this->successMessage = 'Sucursal eliminada exitosamente.';

        } catch (\Exception $e) {
            Log::error('Error deleting warehouse', [
                'warehouse_id' => $id,
                'error' => $e->getMessage()
            ]);

            $this->errorMessage = 'Error al eliminar la sucursal: ' . $e->getMessage();
        }
    }

    public function toggleStatus($id)
    {
        try {
            $this->ensureTenantConnection();

            $warehouse = VntWarehouse::findOrFail($id);
            $newStatus = $warehouse->status ? 0 : 1;
            $warehouse->update(['status' => $newStatus]);

            $this->successMessage = 'Estado de sucursal actualizado exitosamente';

        } catch (\Exception $e) {
            Log::error('Error toggling warehouse status', [
                'warehouse_id' => $id,
                'error' => $e->getMessage()
            ]);

            $this->errorMessage = 'Error al actualizar el estado: ' . $e->getMessage();
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function updateCity($cityId)
    {
        $this->cityId = $cityId;
    }

    public function exportExcel()
    {
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Exportación a Excel - En desarrollo'
        ]);
    }

    public function exportPdf()
    {
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Exportación a PDF - En desarrollo'
        ]);
    }

    public function exportCsv()
    {
        $this->dispatch('show-toast', [
            'type' => 'info',
            'message' => 'Exportación a CSV - En desarrollo'
        ]);
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->companyId = '';
        $this->name = '';
        $this->address = '';
        $this->cityId = '';
        $this->postcode = '';
        $this->branch_type = 'FIJA';
        $this->status = true;
        $this->resetErrorBag();
        $this->clearMessages();
    }

    private function clearMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }
}