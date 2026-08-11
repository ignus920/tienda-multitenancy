<?php

namespace App\Livewire\Tenant\Portal;

use Livewire\Component;
use App\Models\Tenant\Customer\VntCompany;
use App\Models\Tenant\Customer\VntWarehouse;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class PortalWarehouseModal extends Component
{
    public $companyId;
    public $companyName = '';
    public $formMode = null; // null, 'create', 'edit'
    public $editingWarehouseId = null;
    public $warehouseForm = [
        'name' => '',
        'address' => '',
        'cityId' => '',
        'postcode' => '',
        'branch_type' => 'FIJA',
        'phone' => ''
    ];
    public $districtId = null;

    public $successMessage = '';
    public $errorMessage = '';

    protected $listeners = [
        'city-changed' => 'updateCity',
        'district-changed' => 'updateDistrict'
    ];

    public function mount($companyId)
    {
        $this->companyId = $companyId;
        $this->loadCompanyData();
    }

    public function selectWarehouse($warehouseId)
    {
        $this->dispatch('warehouse-selected', branchId: $warehouseId);
        $this->closeModal();
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $warehouses = $this->getWarehouses();
        return view('livewire.tenant.portal.components.portal-warehouse-modal', [
            'warehouses' => $warehouses
        ]);
    }

    public function loadCompanyData()
    {
        $this->ensureTenantConnection();
        $company = VntCompany::findOrFail($this->companyId);
        $this->companyName = $company->businessName ?: trim($company->firstName . ' ' . $company->lastName);
    }

    public function getWarehouses()
    {
        return VntWarehouse::where('companyId', $this->companyId)
            ->with(['city', 'districtRelation'])
            ->orderBy('main', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function startCreateWarehouse()
    {
        $this->formMode = 'create';
        $this->editingWarehouseId = null;
        $this->warehouseForm = [
            'name' => '',
            'address' => '',
            'cityId' => '',
            'postcode' => '',
            'branch_type' => 'FIJA',
            'phone' => ''
        ];
        $this->districtId = null;
        $this->resetErrorBag();
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function editWarehouse($warehouseId)
    {
        $this->ensureTenantConnection();
        $warehouse = VntWarehouse::findOrFail($warehouseId);

        // No permitir editar sucursal principal desde el portal
        if ($warehouse->main) {
            $this->errorMessage = 'La sucursal principal no se puede editar desde el portal de clientes';
            return;
        }

        $this->formMode = 'edit';
        $this->editingWarehouseId = $warehouseId;
        $this->warehouseForm = [
            'name' => $warehouse->name,
            'address' => $warehouse->address,
            'cityId' => $warehouse->cityId,
            'postcode' => $warehouse->postcode ?? '',
            'branch_type' => $warehouse->branch_type ?? 'FIJA',
            'phone' => $warehouse->phone ?? ''
        ];
        $this->districtId = $warehouse->district ?? null;
        $this->resetErrorBag();
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function saveWarehouse()
    {
        $this->validate([
            'warehouseForm.name' => 'required|string|max:255',
            'warehouseForm.address' => 'required|string|max:500',
            'warehouseForm.cityId' => 'required|exists:cities,id',
            'warehouseForm.postcode' => 'nullable|string|max:20',
            'warehouseForm.branch_type' => 'required|in:FIJA,DESPACHO',
            'warehouseForm.phone' => 'nullable|string|max:25',
        ], [
            'warehouseForm.name.required' => 'El nombre es obligatorio',
            'warehouseForm.name.max' => 'El nombre no puede exceder 255 caracteres',
            'warehouseForm.address.required' => 'La dirección es obligatoria',
            'warehouseForm.address.max' => 'La dirección no puede exceder 500 caracteres',
            'warehouseForm.cityId.required' => 'La ciudad es obligatoria',
            'warehouseForm.cityId.exists' => 'La ciudad seleccionada no es válida',
            'warehouseForm.postcode.max' => 'El código postal no puede exceder 20 caracteres',
            'warehouseForm.branch_type.required' => 'El tipo de sucursal es obligatorio',
            'warehouseForm.branch_type.in' => 'El tipo de sucursal debe ser FIJA o DESPACHO',
            'warehouseForm.phone.max' => 'El teléfono no puede exceder 25 caracteres',
        ]);

        try {
            if ($this->formMode === 'create') {
                $this->ensureTenantConnection();
                $warehouse = VntWarehouse::create([
                    'companyId' => $this->companyId,
                    'name' => $this->warehouseForm['name'],
                    'address' => $this->warehouseForm['address'],
                    'cityId' => $this->warehouseForm['cityId'],
                    'postcode' => $this->warehouseForm['postcode'],
                    'branch_type' => $this->warehouseForm['branch_type'],
                    'phone' => $this->warehouseForm['phone'] ?? '',
                    'main' => 0,
                    'status' => 1,
                    'district' => $this->districtId ?: null
                ]);

                $this->successMessage = 'Sucursal agregada exitosamente';
                $this->selectWarehouse($warehouse->id);
                return;
            } else {
                $this->ensureTenantConnection();
                $warehouse = VntWarehouse::findOrFail($this->editingWarehouseId);

                if ($warehouse->main) {
                    $this->errorMessage = 'No se puede editar la sucursal principal';
                    return;
                }

                $warehouse->update([
                    'name' => $this->warehouseForm['name'],
                    'address' => $this->warehouseForm['address'],
                    'cityId' => $this->warehouseForm['cityId'],
                    'postcode' => $this->warehouseForm['postcode'],
                    'branch_type' => $this->warehouseForm['branch_type'],
                    'phone' => $this->warehouseForm['phone'] ?? '',
                    'district' => $this->districtId ?: null
                ]);

                $this->successMessage = 'Sucursal actualizada exitosamente';
            }

            $this->cancelForm();
        } catch (\Exception $e) {
            Log::error('Error saving warehouse from portal', [
                'company_id' => $this->companyId,
                'mode' => $this->formMode,
                'error' => $e->getMessage()
            ]);

            $this->errorMessage = 'Error al guardar la sucursal: ' . $e->getMessage();
        }
    }

    public function cancelForm()
    {
        $this->formMode = null;
        $this->editingWarehouseId = null;
        $this->warehouseForm = [
            'name' => '',
            'address' => '',
            'cityId' => '',
            'postcode' => '',
            'branch_type' => 'FIJA',
            'phone' => ''
        ];
        $this->districtId = null;
        $this->resetErrorBag();
    }

    public function updateCity($cityId, $index = null)
    {
        $this->warehouseForm['cityId'] = $cityId;
    }

    public function updateDistrict($districtId, $index = null)
    {
        $this->districtId = $districtId ?: null;
    }

    public function closeModal()
    {
        $this->dispatch('warehouse-modal-closed');
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
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }
}
