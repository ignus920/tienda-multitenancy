<?php

namespace App\Livewire\Tenant\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Central\VntWarehouse;
use App\Models\Central\VntCompany;
use App\Models\Central\CnfCity;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HasCompanyConfiguration;

class Warehouse extends Component
{
    use WithPagination, \App\Traits\Livewire\WithExport, HasCompanyConfiguration;

    protected $listeners = ['city-changed' => 'updatedCityId', 'city-valid' => 'cityValidate'];

    public $warehouse_id, $companyId, $name, $address, $postcode, $cityId;
    public $billingFormat, $is_credit, $termId, $creditLimit, $status, $main;

    // Propiedades para la tabla
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $showModal = false;
    public $confirmingWarehouseDeletion = false;
    public $warehouseIdToDelete;
    public $perPage = 10;

    // Propiedades para bodegas adicionales
    public $additionalStores = [];
    public $existingStores = [];
    public $newStoreName = '';
    public $showAddStoreInput = false;
    public $storesToDelete = [];
    public $editingStoreIndex = null;
    public $editingStoreName = '';
    public $storeError = '';

    // Datos para selects
    public $companies = [];
    public $cities = [];

    protected $rules = [
        'companyId' => 'required|exists:central.vnt_companies,id',
        'name' => 'required|min:3|max:255',
        'address' => 'nullable|string|max:500',
        'postcode' => 'nullable|string|max:20',
        'cityId' => 'nullable|exists:central.cities,id',
    ];

    protected $validationAttributes = [
        'companyId' => 'compañía',
        'name' => 'nombre del almacén',
        'address' => 'dirección',
        'postcode' => 'código postal',
        'cityId' => 'ciudad',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    protected $messages = [
        'companyId.required' => 'La compañía es obligatoria',
        'companyId.exists' => 'La compañía seleccionada no existe',
        'name.required' => 'El nombre del almacén es obligatorio',
        'name.min' => 'El nombre debe tener al menos 3 caracteres',
        'name.max' => 'El nombre no puede exceder 255 caracteres',
        'address.max' => 'La dirección no puede exceder 500 caracteres',
        'postcode.max' => 'El código postal no puede exceder 20 caracteres',
        'cityId.exists' => 'La ciudad seleccionada no existe',
    ];

    public function mount()
    {
        $this->ensureTenantConnection();
        $this->initializeCompanyConfiguration();
        $this->loadCompanies();
        $this->loadCities();
        $this->is_credit = false;
        $this->main = 2; // 2 = Secundario por defecto
        $this->status = true;
        $this->billingFormat = null;
        $this->termId = null;
        $this->creditLimit = null;
        
        // Obtener el companyId del usuario logueado
        $user = auth()->user();
        if ($user->contact_id) {
            $contact = \App\Models\Central\VntContact::with('warehouse')->find($user->contact_id);
            if ($contact && $contact->warehouse) {
                $this->companyId = $contact->warehouse->companyId;
            }
        }
    }

    public function updatedCityId($cityId, $index = null)
    {
        // Log para debugging
        Log::info('Warehouse: updatedCityId called', [
            'cityId' => $cityId,
            'index' => $index
        ]);
        
        $this->cityId = $cityId;
        
        // Validar que la ciudad sea válida
        if (!empty($cityId) && is_numeric($cityId)) {
            $city = \App\Models\Central\CnfCity::find($cityId);
            if (!$city) {
                $this->addError('cityId', 'La ciudad seleccionada no es válida.');
            } else {
                // Limpiar error si la ciudad es válida
                $this->resetErrorBag('cityId');
            }
        }
    }

    public function cityValidate($index, $cityId = null): bool
    {
        // Este método se llama desde el componente GenericSelect
        // Si cityId viene del evento, usarlo directamente
        $cityIdToValidate = $cityId ?? $this->cityId;

        // Validar que se haya seleccionado una ciudad válida
        if (empty($cityIdToValidate) || !is_numeric($cityIdToValidate)) {
            $this->addError('cityId', 'Debe seleccionar una ciudad válida.');
            return false;
        }

        // Obtener el nombre de la ciudad para validar que existe
        $city = \App\Models\Central\CnfCity::find($cityIdToValidate);
        if (!$city) {
            $this->addError('cityId', 'La ciudad seleccionada no es válida.');
            return false;
        }

        // Actualizar las propiedades si vienen del evento
        if ($cityId !== null) {
            $this->cityId = (int) $cityId;
        }

        // Limpiar error si la ciudad es válida
        $this->resetErrorBag('cityId');
        
        return true;
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

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }

    private function loadCompanies()
    {
        $this->companies = VntCompany::where('status', 1)
            ->orderBy('businessName')
            ->get();
    }

    private function loadCities()
    {
        $this->cities = CnfCity::orderBy('name')
            ->limit(100)
            ->get();
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

    public function create()
    {
        $this->resetForm();
        
        // Obtener el companyId del usuario logueado
        $user = auth()->user();
        if ($user->contact_id) {
            $contact = \App\Models\Central\VntContact::with('warehouse')->find($user->contact_id);
            if ($contact && $contact->warehouse) {
                $this->companyId = $contact->warehouse->companyId;
            }
        }
        
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->ensureTenantConnection();
        $warehouse = VntWarehouse::findOrFail($id);
        
        $this->warehouse_id = $warehouse->id;
        $this->companyId = $warehouse->companyId;
        $this->name = $warehouse->name;
        $this->address = $warehouse->address;
        $this->postcode = $warehouse->postcode;
        $this->cityId = $warehouse->cityId;
        $this->billingFormat = $warehouse->billingFormat;
        $this->is_credit = $warehouse->is_credit;
        $this->termId = $warehouse->termId;
        $this->creditLimit = $warehouse->creditLimit;
        $this->main = $warehouse->main;
        
        // Cargar las bodegas existentes de esta sucursal
        $this->loadExistingStores($id);
        
        $this->showModal = true;
    }

    public function loadExistingStores($warehouseId)
    {
        $this->existingStores = \App\Models\Tenant\Items\InvStore::on('tenant')
            ->where('warehouseId', $warehouseId)
            ->where('status', 1)
            ->get()
            ->toArray();
    }

    public function resetForm()
    {
        $this->warehouse_id = null;
        $this->companyId = null;
        $this->name = '';
        $this->address = '';
        $this->postcode = '';
        $this->cityId = null;
        $this->billingFormat = null;
        $this->is_credit = false;
        $this->termId = null;
        $this->creditLimit = null;
        $this->main = 2; // 2 = Secundario por defecto
        $this->status = true;
        $this->additionalStores = [];
        $this->existingStores = [];
        $this->newStoreName = '';
        $this->showAddStoreInput = false;
        $this->storesToDelete = [];
        $this->editingStoreIndex = null;
        $this->editingStoreName = '';
        $this->storeError = '';

        // Obtener el companyId del usuario logueado
        $user = auth()->user();
        if ($user->contact_id) {
            $contact = \App\Models\Central\VntContact::with('warehouse')->find($user->contact_id);
            if ($contact && $contact->warehouse) {
                $this->companyId = $contact->warehouse->companyId;
            }
        }
    }

    public function addStore()
    {
        if (empty($this->newStoreName)) {
            return;
        }

        $this->storeError = '';
        $this->ensureTenantConnection();
        $storeLimit = $this->getOptionValue(27);
        if ($storeLimit !== null) {
            // Al crear: PRINCIPAL(1) + pending; al editar: existing + pending
            $principalCount = $this->warehouse_id ? 0 : 1;
            $totalAfterAdd  = count($this->existingStores) + count($this->additionalStores) + $principalCount + 1;
            if ($totalAfterAdd > $storeLimit) {
                $this->storeError = "Ha alcanzado el límite de {$storeLimit} bodega(s) por sucursal según su plan.";
                return;
            }
        }

        $this->additionalStores[] = [
            'name' => $this->newStoreName,
        ];

        $this->newStoreName = '';
    }

    public function removeStore($index)
    {
        unset($this->additionalStores[$index]);
        $this->additionalStores = array_values($this->additionalStores);
    }

    public function removeExistingStore($index)
    {
        // Las bodegas existentes NO se pueden eliminar, solo editar
        // Este método ya no se usa para bodegas existentes
        session()->flash('error', 'Las bodegas existentes no pueden ser eliminadas, solo puedes editar su nombre.');
    }

    public function editExistingStore($index)
    {
        if (isset($this->existingStores[$index])) {
            // Proteger la bodega PRINCIPAL - no se puede editar su nombre
            if (strtoupper($this->existingStores[$index]['name']) === 'PRINCIPAL') {
                session()->flash('error', 'La bodega PRINCIPAL no puede ser renombrada.');
                return;
            }
            
            $this->editingStoreIndex = $index;
            $this->editingStoreName = $this->existingStores[$index]['name'];
        }
    }

    public function saveExistingStore()
    {
        if ($this->editingStoreIndex !== null && !empty($this->editingStoreName)) {
            // Validar que el nuevo nombre no esté vacío
            $trimmedName = trim($this->editingStoreName);
            if (empty($trimmedName)) {
                session()->flash('error', 'El nombre de la bodega no puede estar vacío.');
                return;
            }

            // Actualizar el nombre en el array
            $this->existingStores[$this->editingStoreIndex]['name'] = $trimmedName;
            
            // Limpiar el estado de edición
            $this->editingStoreIndex = null;
            $this->editingStoreName = '';
            
            session()->flash('info', 'El nombre será actualizado al guardar los cambios.');
        }
    }

    public function cancelEditExistingStore()
    {
        $this->editingStoreIndex = null;
        $this->editingStoreName = '';
    }

    public function toggleAddStoreInput()
    {
        $this->showAddStoreInput = !$this->showAddStoreInput;
        $this->newStoreName = '';
    }

    public function cancel()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = false;
        $this->confirmingWarehouseDeletion = false;
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->validate();

        // Obtener el companyId del usuario si no está establecido
        if (!$this->companyId) {
            $user = auth()->user();
            if ($user->contact_id) {
                $contact = \App\Models\Central\VntContact::with('warehouse')->find($user->contact_id);
                if ($contact && $contact->warehouse) {
                    $this->companyId = $contact->warehouse->companyId;
                }
            }
        }

        $warehouseData = [
            'companyId' => $this->companyId,
            'name' => $this->name,
            'address' => $this->address,
            'postcode' => $this->postcode,
            'cityId' => $this->cityId,
            'status' => $this->status ?? true,
            'main' => 2, // Siempre secundario por defecto
        ];

        // ── Verificar límite de sucursales (option_id = 26) ──────────────
        if (!$this->warehouse_id) {
            $limit = $this->getOptionValue(26);
            if ($limit !== null) {
                $currentCount = VntWarehouse::where('companyId', $this->companyId)->where('status', 1)->count();
                if ($currentCount >= $limit) {
                    session()->flash('error', "Ha alcanzado el límite de {$limit} sucursal(es) permitidas por su plan.");
                    return;
                }
            }
        }

        // ── Verificar límite de bodegas (option_id = 27) ─────────────────
        $storeLimit = $this->getOptionValue(27);
        if ($storeLimit !== null) {
            if ($this->warehouse_id) {
                // Editando: existentes + nuevas no debe superar el límite
                $total = count($this->existingStores) + count($this->additionalStores);
            } else {
                // Creando: PRINCIPAL + adicionales
                $total = 1 + count($this->additionalStores);
            }
            if ($total > $storeLimit) {
                session()->flash('error', "Ha superado el límite de {$storeLimit} bodega(s) por sucursal según su plan.");
                return;
            }
        }

        DB::connection('central')->beginTransaction();
        DB::connection('tenant')->beginTransaction();

        try {
            if ($this->warehouse_id) {
                // Actualizar sucursal existente
                $warehouse = VntWarehouse::findOrFail($this->warehouse_id);
                $warehouse->update($warehouseData);
                
                // Actualizar nombres de bodegas existentes
                foreach ($this->existingStores as $store) {
                    \App\Models\Tenant\Items\InvStore::on('tenant')
                        ->where('id', $store['id'])
                        ->update(['name' => $store['name']]);
                }
                
                // Agregar nuevas bodegas
                foreach ($this->additionalStores as $store) {
                    \App\Models\Tenant\Items\InvStore::on('tenant')->create([
                        'name' => $store['name'],
                        'warehouseId' => $warehouse->id,
                        'status' => 1,
                    ]);
                }
                
                // Commit de ambas transacciones
                DB::connection('central')->commit();
                DB::connection('tenant')->commit();
                
                $totalStores = count($this->existingStores) + count($this->additionalStores);
                session()->flash('message', 'Almacén actualizado correctamente con ' . $totalStores . ' bodega(s).');
            } else {
                // Crear nueva sucursal en BD central
                $warehouse = VntWarehouse::create($warehouseData);
                
                // Crear bodega PRINCIPAL por defecto en BD tenant
                \App\Models\Tenant\Items\InvStore::on('tenant')->create([
                    'name' => 'PRINCIPAL',
                    'warehouseId' => $warehouse->id,
                    'status' => 1,
                ]);

                // Crear bodegas adicionales si existen
                foreach ($this->additionalStores as $store) {
                    \App\Models\Tenant\Items\InvStore::on('tenant')->create([
                        'name' => $store['name'],
                        'warehouseId' => $warehouse->id,
                        'status' => 1,
                    ]);
                }

                // Commit de ambas transacciones
                DB::connection('central')->commit();
                DB::connection('tenant')->commit();

                session()->flash('message', 'Almacén creado correctamente con ' . (count($this->additionalStores) + 1) . ' bodega(s).');
            }

            $this->resetValidation();
            $this->resetForm();
            $this->showModal = false;
            
        } catch (\Exception $e) {
            // Rollback de ambas transacciones en caso de error
            DB::connection('central')->rollBack();
            DB::connection('tenant')->rollBack();
            
            Log::error('Error al guardar almacén y bodegas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'warehouse_id' => $this->warehouse_id ?? 'new',
                'warehouse_name' => $this->name
            ]);
            
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
            return;
        }
    }

    public function toggleWarehouseStatus($id)
    {
        $this->ensureTenantConnection();
        $warehouse = VntWarehouse::findOrFail($id);

        $newStatus = $warehouse->status ? 0 : 1;
        $warehouse->update(['status' => $newStatus]);

        session()->flash('message', 'Estado actualizado correctamente');
    }

    public function confirmWarehouseDeletion($id)
    {
        $this->confirmingWarehouseDeletion = true;
        $this->warehouseIdToDelete = $id;
    }

    public function deleteWarehouse()
    {
        $this->ensureTenantConnection();

        $warehouseData = [
            'status' => 0,
            'deleted_at' => Carbon::now(),
        ];

        $warehouse = VntWarehouse::findOrFail($this->warehouseIdToDelete);
        $warehouse->update($warehouseData);
        
        $this->confirmingWarehouseDeletion = false;
        $this->reset(['warehouseIdToDelete']);
        session()->flash('message', 'Almacén eliminado correctamente');
    }

    public function render()
    {
        $this->ensureTenantConnection();
        
        $user = auth()->user();
        
        $warehouses = VntWarehouse::query()
            ->with(['company'])
            ->when(!$user->isSuperAdmin() && $user->contact_id, function ($query) use ($user) {
                // Filtrar por todas las sucursales de la compañía del usuario
                $contact = \App\Models\Central\VntContact::with('warehouse.company')->find($user->contact_id);
                if ($contact && $contact->warehouse && $contact->warehouse->companyId) {
                    $query->where('companyId', $contact->warehouse->companyId);
                }
            })
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%')
                      ->orWhereHas('company', function($companyQuery) {
                          $companyQuery->where('businessName', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Agregar los inv_store asociados a cada warehouse desde la BD tenant
        foreach ($warehouses as $warehouse) {
            $warehouse->stores = \App\Models\Tenant\Items\InvStore::on('tenant')
                ->where('warehouseId', $warehouse->id)
                ->where('status', 1)
                ->get();
        }

        $warehouseLimit = $this->getOptionValue(26);
        $warehouseCount = $this->companyId
            ? VntWarehouse::where('companyId', $this->companyId)->where('status', 1)->count()
            : 0;

        return view('livewire.tenant.inventory.warehouse', [
            'warehouses'      => $warehouses,
            'warehouseLimit'  => $warehouseLimit,
            'warehouseCount'  => $warehouseCount,
            'limitReached'    => $warehouseLimit !== null && $warehouseCount >= $warehouseLimit,
            'storeLimit'      => $this->getOptionValue(27),
        ]);
    }

    /**
     * Métodos para Exportación
     */
    protected function getExportData()
    {
        $this->ensureTenantConnection();
        
        $user = auth()->user();
        
        return VntWarehouse::query()
            ->with(['company'])
            ->when(!$user->isSuperAdmin() && $user->contact_id, function ($query) use ($user) {
                // Filtrar por todas las sucursales de la compañía del usuario
                $contact = \App\Models\Central\VntContact::with('warehouse.company')->find($user->contact_id);
                if ($contact && $contact->warehouse && $contact->warehouse->companyId) {
                    $query->where('companyId', $contact->warehouse->companyId);
                }
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('address', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();
    }

    protected function getExportHeadings(): array
    {
        return ['ID', 'Compañía', 'Nombre', 'Dirección', 'Ciudad', 'Principal', 'Estado', 'Fecha Registro'];
    }

    protected function getExportMapping()
    {
        return function ($warehouse) {
            return [
                $warehouse->id,
                $warehouse->company->businessName ?? 'N/A',
                $warehouse->name,
                $warehouse->address ?? 'N/A',
                $warehouse->cityId ?? 'N/A',
                $warehouse->main ? 'Sí' : 'No',
                $warehouse->status ? 'Activo' : 'Inactivo',
                $warehouse->created_at ? Carbon::parse($warehouse->created_at)->format('Y-m-d H:i:s') : 'N/A',
            ];
        };
    }

    protected function getExportFilename(): string
    {
        return 'almacenes_' . now()->format('Y-m-d_His');
    }
}
