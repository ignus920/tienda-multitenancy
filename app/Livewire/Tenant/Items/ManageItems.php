<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\Livewire\WithExport;
//Modelos
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\Category;
use App\Models\Tenant\Items\InvValues;
use App\Models\Tenant\Items\InvItemsStore;
use App\Models\Tenant\Items\InvStore;
use App\Models\Auth\UserTenant;
use App\Models\Auth\Tenant;
use App\Models\Central\VntWarehouse;
use App\Models\Tenant\CnfTaxes;
//Servicios
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\Inventory\CategoriesService;
use App\Services\Tenant\Inventory\CommandsServices;
use App\Livewire\Tenant\Items\Services\InvValuesService;
use App\Services\Facturacion\DatabaseConfigService;
use App\Services\Facturacion\ApiClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasCompanyConfiguration;
use Livewire\Attributes\On;
use App\Traits\Livewire\HasDynamicButtons;
use Livewire\Attributes\Computed;

class ManageItems extends Component
{

    use WithPagination, HasCompanyConfiguration, WithExport, HasDynamicButtons;

    public $selectedSupplierId = null;

    protected $listeners = [
        'command-changed' => 'onCommandSelected',
        'command-created' => 'refreshCommands',
        'brand-changed' => 'onBrandSelected',
        'brand-created' => 'refreshBrands',
        'house-changed' => 'onHouseSelected',
        'purchase-unit-changed' => 'onPurchaseUnitSelected',
        'consumption-unit-changed' => 'onConsumptionUnitSelected',
        'category-changed' => 'onCategorySelected',
        'category-created' => 'refreshCategories',
        'closeValuesModal' => 'closeValuesModal',
        'closeLocationsModal' => 'closeLocationsModal',
        'refreshProductList' => '$refresh',
        //'invValuesItem-created' => 'refreshValuesItems',
    ];

    // Propiedades para el formulario
    public $item_id;
    public $category_id;
    public $name;
    public $internal_code;
    public $sku;
    public $description;
    public $type;
    public $commandId;
    public $brandId;
    public $houseId;
    public $purchase_unit;
    public $consumption_unit;
    public $generic = 1;
    public $inv_values = [];
    public $warehouses = [];
    public $warehouseIdValue;
    public $locationName;
    public $tax;
    public $disabled = false;
    public $handles_serial;
    public $inventoriable;
    public $wpStockPercentage = 100;
    public $wpMinStock = 0;
    public $maxLocationsCount = 0;
    protected $exportSuppliers = [];
    public $tempValues = [];

    // Propiedades para modal de ubicaciones
    public $showLocationsModal = false;
    public $selectedItemId;

    // Propiedades para modal de stock
    public $showStockModal = false;
    public $selectedItemForStock;
    public $selectedItemName;
    public $selectedItemSku;
    public $stockByWarehouse = [];

    // Propiedades para la tabla
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $showModal = false;
    public $confirmingItemDeletion = false;
    public $perPage = 10;

    //Información para categorias
    public $showCategoryInput = false;
    public $newCategoryName = '';

    //Información para comandas
    public $showCommandInput = false;
    public $newCommandName = '';

    //Información precios
    public $showValuesSection = false;
    public $valueItem = 0;
    public $typeValue;
    public $labelValue;
    public $messageValues = '';
    public $temporaryErrorMessage;
    public $showValuesModal = false;

    //Campos de validación
    public $internal_codeExists = false;
    public $validatingInternal_code = false;
    public $skuExists = false;
    public $validatingSku = false;
    public $showCommand = false;
    public $showSelectStore = false;
    public $showProductionSection = false;
    public $showDimensionSection = false;
    public $showAccesoriosSection = false;
    public $moduleKey = 'items';

    // tipos disponibles (puedes externalizarlo si lo prefieres)
    public $types = [
        'COMBO'           => 'Combo',
        'COMPRA NACIONAL' => 'Compra nacional',
        'IMPORTADO'       => 'Importado',
        'PRODUCIDO'       => 'Producido',
        'INSUMO'          => 'Insumo',
        'ENSAMBLADO'      => 'Ensamblado',
        'PROYECTADOS'     => 'Proyectados',
        'DESCONTINUADOS'  => 'Descontinuados',
        'CZCL'            => 'CZCL',
    ];

    public $allLabelsValues = [
        'costo' => [
            'Costo Inicial' => 'Costo Inicial',
            'Costo' => 'Costo',
        ],
        'precio' => [
            'Precio Base' => 'Precio Lista',
            'Precio Regular' => 'Precio Mínimo',
            'Precio Crédito' => 'Precio Crédito',
            'Precio unitario x caja' => 'Precio unitario x caja',
        ],
    ];

    public function getLabelsValuesProperty()
    {
        return $this->allLabelsValues[$this->typeValue] ?? [];
    }

    public function updatedTypeValue($value)
    {
        $this->labelValue = null; // Reset labelValue when typeValue changes
    }

    protected $rules = [
        'category_id' => 'required|integer|exists:tenant.inv_categories,id',
        'name' => 'required|string|min:3|max:255',
        'type' => 'required|string',
        'internal_code' => 'required|string|max:100',
        'brandId' => 'required|integer|min:1',
        'houseId' => 'required|integer|min:1',
        'purchase_unit' => 'required|integer|min:1',
        'consumption_unit' => 'required|integer|min:1',
        'tax' => 'required|integer|min:1',
        'sku' => 'nullable|string|max:100',
        'description' => 'nullable|string|max:1000',
        'inventoriable' => 'nullable|boolean',
    ];

    protected $messages = [
        'category_id.required' => 'La categoría es obligatoria',
        'category_id.exists' => 'La categoría seleccionada no es válida',
        'name.required' => 'El nombre del item es obligatorio',
        'name.min' => 'El nombre del item debe tener al menos 3 caracteres',
        'name.max' => 'El nombre del item no puede exceder 255 caracteres',
        'type.required' => 'El tipo de item es obligatorio',
        'internal_code.required' => 'El código interno es obligatorio',
        'internal_code.max' => 'El código interno no puede exceder 100 caracteres',
        'brandId.required' => 'La marca es obligatoria',
        'brandId.min' => 'Debe seleccionar una marca válida',
        'houseId.required' => 'La casa es obligatoria',
        'houseId.min' => 'Debe seleccionar una casa válida',
        'purchase_unit.required' => 'La unidad de compra es obligatoria',
        'purchase_unit.min' => 'Debe seleccionar una unidad de compra válida',
        'consumption_unit.required' => 'La unidad de consumo es obligatoria',
        'consumption_unit.min' => 'Debe seleccionar una unidad de consumo válida',
        'tax.required' => 'El impuesto es obligatorio',
        'tax.min' => 'Debe seleccionar un impuesto válido',
        'sku.max' => 'El SKU no puede exceder 100 caracteres',
        'description.max' => 'La descripción no puede exceder 1000 caracteres',
    ];

    /**
     * Validación en tiempo real de campos individuales
     */
    public function updated($propertyName)
    {
        // Lista de campos que deben validarse en tiempo real
        $fieldsToValidate = [
            'category_id',
            'name',
            //'type',
            'internal_code',
            'brandId',
            'houseId',
            'purchase_unit',
            'consumption_unit',
            'tax',
            'sku',
            'description'
        ];

        if (in_array($propertyName, $fieldsToValidate)) {
            $this->validateOnly($propertyName);
        }
    }


    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
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
        /*$this->sortDirection = $this->sortField === $field 
            ? $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'
            : 'asc';

        $this->sortField = $field;*/
    }

    public function mount()
    {
        $this->ensureTenantConnection();
        $this->validateMerchantType();
        // DEBUG: Limpiar caché para testing
        $this->clearConfigurationCache();

        if (Auth::user()?->profile_id == 17) {
            $this->selectedSupplierId = Auth::id();
        }

        // DEBUG: Log para verificar inicialización
        Log::info('🔍 Items mount() ejecutado', [
            'currentCompanyId' => $this->currentCompanyId,
            'currentPlainId' => $this->currentPlainId,
            'configService_exists' => $this->configService ? 'YES' : 'NO'
        ]);
    }

    public function updatedSelectedSupplierId($value)
    {
        $this->resetPage();
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

    public function manageSerials()
    {
        $this->initializeCompanyConfiguration();
        $result = $this->isOptionEnabled(32);
        $value = $this->getOptionValue(32);

        Log::info('🔍 manageSerials() verificación', [
            'companyId' => $this->currentCompanyId,
            'option_id' => 32,
            'result' => $result ? 'TRUE' : 'FALSE',
            'option_value' => $value,
            'configService_exists' => $this->configService ? 'YES' : 'NO',
            'method_called' => 'isOptionEnabled(32) y getOptionValue(32)'
        ]);
        return $result;
    }

    public function edit($idItem)
    {
        $this->clearValidationErrors();
        $this->ensureTenantConnection();
        $item = Items::with('invValues')->findOrFail($idItem);
        $this->item_id = $item->id;
        $this->category_id = $item->categoryId;
        $this->name = $item->name;
        $this->internal_code = $item->internal_code;
        $this->sku = $item->sku ?? null;
        $this->description = $item->description;
        $this->type = $item->type;
        $this->commandId = $item->commandId;
        $this->brandId = $item->brandId;
        $this->houseId = $item->houseId;
        $this->purchase_unit = $item->purchasing_unit;
        $this->consumption_unit = $item->consumption_unit;
        $this->generic = $item->generic ?? 1;
        $this->tax = $item->taxId;
        $this->handles_serial = $item->handles_serial;
        $this->inventoriable = $item->inventoriable;

        if ($item->inventoriable == 1) {
            $storeRecord = InvItemsStore::where('itemId', $item->id)->where('storeId', 2)->first();
            $this->wpStockPercentage = $storeRecord?->wp_stock_percentage ?? 100;
            $this->wpMinStock = $storeRecord?->wp_min_stock ?? 0;
        }

        $this->disabled = true;
        $this->showProductionSection = false;
        $this->showDimensionSection = false;
        $this->showAccesoriosSection = false;

        $this->showModal = true;
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $this->loadWarehouses();

        $items = Items::query()
            ->with(['brand', 'principalImage', 'purchasingUnit', 'consumptionUnit', 'tax'])
            ->when($this->selectedSupplierId, function ($query) {
                $query->whereHas('importSetup', function ($q) {
                    $q->where('supplier_id', $this->selectedSupplierId);
                });
            })
            ->when($this->search, function ($query) {
                $words = array_filter(explode(' ', trim($this->search)));
                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->where('name', 'like', '%' . $word . '%')
                            ->orWhere('sku', 'like', '%' . $word . '%')
                            ->orWhere('internal_code', 'like', '%' . $word . '%')
                            ->orWhere('type', 'like', '%' . $word . '%');
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.items.manage-items', [
            'items' => $items,
            'categories' => Category::where('status', 1)->get(),
            'types' => $this->types
        ]);
    }

    #[Computed]
    public function suppliers()
    {
        $this->ensureTenantConnection();
        $sessionTenant = session('tenant_id');

        return \App\Models\Auth\User::select('users.id', 'users.name')
            ->join('vnt_contacts', 'users.contact_id', '=', 'vnt_contacts.id')
            ->whereHas('tenants', function ($query) use ($sessionTenant) {
                $query->where('tenants.id', $sessionTenant);
            })
            ->where('users.profile_id', 17)
            ->where('vnt_contacts.status', 1)
            ->whereNull('vnt_contacts.deleted_at')
            ->distinct()
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'firstName' => $supplier->name
                ];
            })
            ->toArray();
    }

    public function toggleGeneric()
    {
        $this->generic = $this->generic ? 0 : 1;
    }

    public function create()
    {
        $this->resetExcept(['categories', 'types', 'allLabelsValues', 'showCommand']); // No reseteamos las listas de opciones
        $this->showModal = true;

        // Emitir eventos para inicializar los componentes hijos
        $this->dispatch('initializeCommand');
        $this->dispatch('initializeBrand');
        $this->dispatch('initializeHouse');
        $this->dispatch('initializePurchaseUnit');
        $this->dispatch('initializeConsumptionUnit');

        $this->clearValidationErrors();
        $this->resetForm();
        Log::info('🔒 Show Campo Comanda Crear: ' . $this->showCommand);
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->validate();

        if ($this->internal_codeExists) {
            $this->addError('internal_code', 'Este código interno ya está registrado.');
            return;
        }

        if ($this->skuExists) {
            $this->addError('sku', 'Este SKU ya está registrado.');
            return;
        }

        $itemData = [
            'categoryId' => $this->category_id,
            'name' => $this->name,
            'internal_code' => $this->internal_code,
            'sku' => $this->sku,
            'description' => $this->description,
            'type' => $this->type,
            'commandId' => $this->commandId && $this->commandId > 0 ? (int)$this->commandId : null,
            'brandId' => (int)$this->brandId,
            'houseId' => (int)$this->houseId,
            'inventoriable' => $this->inventoriable,
            'purchasing_unit' => $this->purchase_unit,
            'consumption_unit' => $this->consumption_unit,
            'status' => 1,
            'generic' => 0,
            'taxId' => (int)$this->tax,

        ];

        try {
            if ($this->item_id) { // Existing item
                $existsValue = InvValues::where('itemId', $this->item_id)->exists();

                if (!$existsValue) {
                    $this->messageValues = 'Tiene que registrar al menos un valor.';
                } else {
                    $item = Items::findOrFail($this->item_id);
                    $wasInventoriable = $item->inventoriable;
                    $item->update($itemData);

                    // Verificar si cambió a inventoriable y crear registro en inv_items_store si es necesario
                    if ($item->inventoriable == 1 && $wasInventoriable != 1) {
                        Log::info('Item actualizado a inventoriable - creando registro en inv_items_store', [
                            'item_id' => $item->id,
                            'was_inventoriable' => $wasInventoriable,
                            'now_inventoriable' => $item->inventoriable
                        ]);
                        $this->createItemStore($item);
                    } elseif ($item->inventoriable == 1) {
                        // Ya era inventoriable, verificar si ya tiene registro (por si acaso)
                        $existingRecord = InvItemsStore::where('itemId', $item->id)->where('storeId', 2)->first();
                        if (!$existingRecord) {
                            Log::warning('Item inventoriable sin registro en inv_items_store - creando', [
                                'item_id' => $item->id
                            ]);
                            $this->createItemStore($item);
                        } else {
                            // Actualizar wp_stock_percentage y wp_min_stock
                            $existingRecord->update([
                                'wp_stock_percentage' => max(0, min(100, (float) $this->wpStockPercentage)),
                                'wp_min_stock'        => max(0, (float) $this->wpMinStock),
                            ]);
                        }
                    }

                    // Sincronizar con API de facturación (con timeout)
                    try {
                        set_time_limit(10); // Máximo 10 segundos para sincronización
                        $syncResult = $this->syncItemWithApi($item);
                        set_time_limit(60); // Restaurar timeout normal

                        // Mostrar mensaje de éxito o advertencia al usuario
                        if ($syncResult['success']) {
                            session()->flash('sync_message', '✅ Item actualizado y sincronizado con la API de facturación correctamente.');
                            $this->showModal = false; // Solo cerrar modal si sincronización fue exitosa
                        } else {
                            session()->flash('sync_warning', '⚠️ Item actualizado localmente, pero falló la sincronización con API: ' . $syncResult['message']);
                            // NO cerrar modal para que el usuario vea el mensaje
                        }
                    } catch (\Exception $e) {
                        set_time_limit(60); // Restaurar timeout normal
                        Log::error('Timeout o error en sincronización de item (actualización)', [
                            'item_id' => $item->id,
                            'error' => $e->getMessage()
                        ]);
                        session()->flash('sync_error', '❌ Item actualizado localmente, pero falló la sincronización con API de facturación. Error: ' . $e->getMessage());
                        // NO cerrar modal para que el usuario vea el mensaje de error
                    }

                    $this->clearTemporaryMessage();

                    // Solo cerrar modal si no hay errores de sincronización
                    if (!session()->has('sync_warning') && !session()->has('sync_error')) {
                        session()->flash('success', '✅ ¡Item actualizado exitosamente! El item "' . $item->name . '" ha sido actualizado correctamente.');
                        $this->showModal = false; // Close modal after update
                        $this->resetValidation(); // Clear validation errors for next open
                        $this->resetForm(); // Clear the form completely for next new item
                    } else {
                        session()->flash('message', 'Item actualizado correctamente.');
                    }
                }
            } else { // New item
                // Validar precios según si es inventoriable o no
                if ($this->inventoriable == 1) {
                    // Si es inventoriable, requiere todos los precios
                    $requiredPrices = ['Costo Inicial', 'Costo', 'Precio Base', 'Precio Regular', 'Precio Crédito'];
                } else {
                    // Si NO es inventoriable, solo requiere Costo Inicial
                    $requiredPrices = ['Costo Inicial'];
                }

                $missingPrices = $this->validateRequiredPrices($requiredPrices);

                if (!empty($missingPrices)) {
                    $this->messageValues = 'Debe registrar todos los precios requeridos: ' . implode(', ', $missingPrices);
                } else {
                    $newItem = Items::create($itemData);
                    $item_id = $newItem->id;
                    $this->saveTemporaryValues($item_id);

                    // Crear registro en inv_items_store con el store principal SOLO si es inventoriable
                    if ($newItem->inventoriable == 1) {
                        $this->createItemStore($newItem);
                    } else {
                        Log::info('Item no inventoriable - omitiendo creación en inv_items_store', [
                            'item_id' => $newItem->id,
                            'item_name' => $newItem->name,
                            'inventoriable' => $newItem->inventoriable
                        ]);
                    }

                    // Sincronizar con API de facturación (con timeout)
                    try {
                        set_time_limit(10); // Máximo 10 segundos para sincronización
                        $syncResult = $this->syncItemWithApi($newItem);
                        set_time_limit(60); // Restaurar timeout normal

                        // Mostrar mensaje de éxito o advertencia al usuario
                        if ($syncResult['success']) {
                            session()->flash('sync_message', '✅ Item creado y sincronizado con la API de facturación correctamente.');
                        } else {
                            session()->flash('sync_warning', '⚠️ Item creado localmente, pero falló la sincronización con API: ' . $syncResult['message']);
                            // NO cerrar modal para que el usuario vea el mensaje
                        }
                    } catch (\Exception $e) {
                        set_time_limit(60); // Restaurar timeout normal
                        Log::error('Timeout o error en sincronización de item', [
                            'item_id' => $newItem->id,
                            'error' => $e->getMessage()
                        ]);
                        session()->flash('sync_error', '❌ Item creado localmente, pero falló la sincronización con API de facturación. Error: ' . $e->getMessage());
                        // NO cerrar modal para que el usuario vea el mensaje de error
                    }

                    // Solo proceder a editar si no hay errores de sincronización
                    if (!session()->has('sync_warning') && !session()->has('sync_error')) {
                        session()->flash('success', '✅ ¡Item creado exitosamente! El item "' . $newItem->name . '" ha sido registrado correctamente.');
                        $this->resetValidation(); // Clear validation for current submission
                        $this->edit($item_id); // Load new item into the form (sets showModal=true, disabled=true)
                    } else {
                        session()->flash('message', 'Item creado correctamente.');
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al guardar item: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
            return;
        }
    }


    public function toggleItemStatus($id)
    {
        $this->ensureTenantConnection();
        $item = Items::findOrFail($id);

        $newStatus = $item->status ? 0 : 1;
        $item->update([
            'status' => $newStatus,
        ]);

        session()->flash('message', 'Estado actualizado correctamente');
    }

    public function openValuesModal($itemId)
    {
        $this->item_id = $itemId;
        $this->showValuesModal = true;
    }

    public function closeValuesModal()
    {
        $this->showValuesModal = false;
    }

    public function openLocationsModal($itemId)
    {
        $this->selectedItemId = $itemId;
        $this->showLocationsModal = true;
    }

    public function closeLocationsModal()
    {
        $this->showLocationsModal = false;
        $this->selectedItemId = null;
        $this->locationName = '';
    }

    public function openStockModal($itemId)
    {
        $this->selectedItemForStock = $itemId;
        $this->ensureTenantConnection();
        // Cargar información del item
        $item = Items::find($itemId);
        if ($item) {
            $this->selectedItemName = $item->name;
            $this->selectedItemSku = $item->sku;
        }

        $this->loadStockByWarehouse($itemId);
        $this->showStockModal = true;
    }

    public function closeStockModal()
    {
        $this->showStockModal = false;
        $this->selectedItemForStock = null;
        $this->selectedItemName = null;
        $this->selectedItemSku = null;
        $this->stockByWarehouse = [];
    }

    private function loadStockByWarehouse($itemId)
    {
        try {
            $this->ensureTenantConnection();

            // Obtener todos los registros de inv_items_store para este item
            $itemStores = InvItemsStore::where('itemId', $itemId)
                ->with('store')
                ->get();

            $this->stockByWarehouse = [];

            foreach ($itemStores as $itemStore) {
                if ($itemStore->store) {
                    // Obtener el warehouse asociado al store desde la BD central
                    $warehouse = VntWarehouse::on('central')
                        ->where('id', $itemStore->store->warehouseId)
                        ->first();

                    if ($warehouse) {
                        $warehouseId = $warehouse->id;

                        // Si el warehouse no existe en el array, inicializarlo
                        if (!isset($this->stockByWarehouse[$warehouseId])) {
                            $this->stockByWarehouse[$warehouseId] = [
                                'warehouse_name' => $warehouse->name,
                                'warehouse_id' => $warehouseId,
                                'stores' => []
                            ];
                        }

                        // Agregar el store con su stock
                        $this->stockByWarehouse[$warehouseId]['stores'][] = [
                            'store_id' => $itemStore->store->id,
                            'store_name' => $itemStore->store->name,
                            'stock' => $itemStore->stock_items_store ?? 0,
                            'stock_min' => $itemStore->stock_min ?? 0,
                            'stock_max' => $itemStore->stock_max ?? 0,
                        ];
                    }
                }
            }

            Log::info('Stock cargado por warehouse', [
                'item_id' => $itemId,
                'warehouses_count' => count($this->stockByWarehouse)
            ]);
        } catch (\Exception $e) {
            Log::error('Error cargando stock por warehouse', [
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            $this->stockByWarehouse = [];
        }
    }

    #[On('closeItemsModal')]
    public function cancel()
    {
        $this->ensureTenantConnection();
        if ($this->item_id) {
            $existsValue = InvValues::where('itemId', $this->item_id)->exists();
            if (!$existsValue) {
                $this->messageValues = 'Tiene que registrar al menos un valor.';
            } else {
                $this->resetValidation();
                $this->resetForm();
                $this->showModal = false;
                $this->confirmingItemDeletion = false;
            }
        } else {
            $this->resetValidation();
            $this->resetForm();
            $this->showModal = false;
            $this->confirmingItemDeletion = false;
        }
    }

    public function onCategorySelected($value)
    {
        $this->category_id = $value;
    }

    public function onCommandSelected($value)
    {
        $this->commandId = $value;
    }

    public function onBrandSelected($value)
    {
        $this->brandId = $value;
    }

    public function onHouseSelected($value)
    {
        $this->houseId = $value;
    }

    public function onPurchaseUnitSelected($value)
    {
        $this->purchase_unit = $value;
    }

    public function onConsumptionUnitSelected($value)
    {
        $this->consumption_unit = $value;
    }

    public function getExportData()
    {
        $this->ensureTenantConnection();
        $items = Items::query()
            ->with(['brand', 'tax', 'purchasingUnit', 'consumptionUnit', 'invItemsStore', 'locations.store', 'invValues', 'importSetup', 'dimensions'])
            ->when($this->search, function ($query) {
                $words = array_filter(explode(' ', trim($this->search)));
                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->where('name', 'like', '%' . $word . '%')
                            ->orWhere('sku', 'like', '%' . $word . '%')
                            ->orWhere('internal_code', 'like', '%' . $word . '%');
                    });
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        // Calcular el número máximo de ubicaciones asignadas a cualquier ítem
        $this->maxLocationsCount = $items->map(fn($item) => $item->locations->count())->max() ?? 0;

        // Cargar nombres de proveedores en memoria
        $supplierIds = $items->pluck('importSetup.supplier_id')->filter()->unique()->toArray();
        if (!empty($supplierIds)) {
            $this->exportSuppliers = \App\Models\Auth\User::whereIn('id', $supplierIds)
                ->pluck('name', 'id')
                ->toArray();
        } else {
            $this->exportSuppliers = [];
        }

        return $items;
    }

    public function getExportHeadings(): array
    {
        return [
            'SKU',
            'Código Interno',
            'Nombre',
            'Tipo',
            'Marca',
            'Stock',
            'Unidad Compra',
            'Unidad Consumo',
            'Impuesto',
            'Estado',
            'Maneja Inventario',
            '% Stock WordPress',
            'Precio Lista',
            'Precio Mínimo',
            'Precio Crédito',
            'Precio x caja',
            'Proveedor',
            'Ref fábrica',
            'EXW',
            'Peso',
            'Cantidad por caja',
            'Ubicación 1 PICKING',
            'Stock PICKING',
            'Ubicación 2 MINIMOS',
            'Stock MINIMOS',
            'Ubicación 3 RESERVAS',
            'Stock RESERVAS'
        ];
    }

    public function getExportColumnFormats(): array
    {
        return [
            'F' => '#,##0',       // Stock General (Fuerza a mostrar el 0)
            'M' => '"$"#,##0.00',
            'N' => '"$"#,##0.00',
            'O' => '"$"#,##0.00',
            'P' => '"$"#,##0.00',
            'S' => '"$"#,##0.00', // EXW
            'W' => '#,##0',       // Stock PICKING (Fuerza a mostrar el 0)
            'Y' => '#,##0',       // Stock MINIMOS (Fuerza a mostrar el 0)
            'AA' => '#,##0',      // Stock RESERVAS (Fuerza a mostrar el 0)
        ];
    }

    public function getExportMapping($item): array
    {
        // Determinar stock (Forzado a cadena '0' si es 0 para obligar a Excel a mostrarlo)
        $stockSum = $item->invItemsStore->isNotEmpty() 
            ? $item->invItemsStore->sum('stock_items_store') 
            : 0;
        $stock = ($stockSum !== null && (int) $stockSum !== 0) ? (int) $stockSum : '0';

        // Obtener valores de precios mapeados
        $precios = [
            'Precio Base' => 0.0,
            'Precio Regular' => 0.0,
            'Precio Crédito' => 0.0,
            'Precio unitario x caja' => 0.0,
        ];
        foreach ($item->invValues as $val) {
            if (array_key_exists($val->label, $precios)) {
                $precios[$val->label] = is_numeric($val->values) ? (float) $val->values : 0.0;
            }
        }

        // Obtener nombre del proveedor asignado
        $supplierName = 'N/A';
        if ($item->importSetup && $item->importSetup->supplier_id) {
            $supplierName = $this->exportSuppliers[$item->importSetup->supplier_id] ?? 'N/A';
        }

        // Fila base con todos los parámetros
        $row = [
            $item->sku,
            $item->internal_code ?? $item->internalCode ?? '',
            $item->name,
            $item->type,
            $item->brand->name ?? 'SIN MARCA',
            $stock,
            $item->purchasingUnit->description ?? 'N/A',
            $item->consumptionUnit->description ?? 'N/A',
            $item->tax->name ?? 'Sin impuesto',
            $item->status ? 'Activo' : 'Inactivo',
            $item->inventoriable == 1 ? 'Sí' : 'No',
            $item->inventoriable == 1 ? ($item->invItemsStore->firstWhere('storeId', 2)->wp_stock_percentage ?? 100) . '%' : 'N/A',
            $precios['Precio Base'],
            $precios['Precio Regular'],
            $precios['Precio Crédito'],
            $precios['Precio unitario x caja'],
            $supplierName,
            $item->importSetup->factory_ref ?? 'N/A',
            is_numeric($item->importSetup->exw ?? null) ? (float) $item->importSetup->exw : 0.0,
            is_numeric($item->dimensions->weight ?? null) ? (float) $item->dimensions->weight : 0.0,
            (int) ($item->dimensions->quntityxbox ?? 0),
        ];

        // Agregar ubicaciones específicas por bodega: PICKING, MINIMOS, RESERVAS (Ubicación y Stock por separado)
        $itemLocations = $item->locations ?? collect([]);

        // 1. PICKING
        $locPicking = $itemLocations->first(fn($l) => str_contains(strtoupper($l->store->name ?? ''), 'PICKING'));
        if ($locPicking) {
            $row[] = $locPicking->locationId ?? '';
            $row[] = ((int) $locPicking->stock_item_location !== 0) ? (int) $locPicking->stock_item_location : '0';
        } else {
            $row[] = '';
            $row[] = '0';
        }

        // 2. MINIMOS
        $locMinimos = $itemLocations->first(fn($l) => str_contains(strtoupper($l->store->name ?? ''), 'MINIMOS'));
        if ($locMinimos) {
            $row[] = $locMinimos->locationId ?? '';
            $row[] = ((int) $locMinimos->stock_item_location !== 0) ? (int) $locMinimos->stock_item_location : '0';
        } else {
            $row[] = '';
            $row[] = '0';
        }

        // 3. RESERVAS
        $locReservas = $itemLocations->first(fn($l) => str_contains(strtoupper($l->store->name ?? ''), 'RESERVAS'));
        if ($locReservas) {
            $row[] = $locReservas->locationId ?? '';
            $row[] = ((int) $locReservas->stock_item_location !== 0) ? (int) $locReservas->stock_item_location : '0';
        } else {
            $row[] = '';
            $row[] = '0';
        }

        return $row;
    }

    public function getExportFilename(): string
    {
        return 'items_' . date('Y-m-d_His');
    }

    public function getTaxesProperty()
    {
        $this->ensureTenantConnection();

        return CnfTaxes::all();
    }

    private function loadWarehouses(): void
    {
        $sessionTenant = $this->getTenantId();

        // Obtener el tenant desde la base de datos usando el ID de sesión
        $tenant = Tenant::find($sessionTenant);

        if (!$tenant || !$tenant->company_id) {
            $this->warehouses = collect([]);
            return;
        }

        // Las bodegas están en la base de datos central (vnt_warehouses)
        // Traer todos los almacenes que coincidan con ese company_id
        $this->warehouses = VntWarehouse::where('companyId', $tenant->company_id)
            ->where('status', true)
            ->with('company')
            ->orderBy('name')
            ->get();
    }

    private function getTenantId()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }
        return $tenantId;
    }

    //============CATEGORIAS========================//
    public function toggleCategoryInput()
    {
        $this->showCategoryInput = ! $this->showCategoryInput;
        if ($this->showCategoryInput) {
            $this->resetValidation();
            $this->newCategoryName = '';
        }
    }

    public function saveCategory()
    {
        $this->ensureTenantConnection();
        try {
            // Usar el servicio para crear la categoría
            $commandService = app(CommandsServices::class);
            $command = $commandService->createCommand([
                'name' => $this->newCommandName,
                'print_path' => 'http://127.0.0.1:8000/inventory/commands',
                'status' => 1,
            ]);

            // Actualizar la lista de categorías y seleccionar la nueva
            $this->commandId = $command->id;

            // Resetear el formulario de categoría
            $this->showCommandInput = false;
            $this->newCommandName = '';

            // Emitir evento para actualizar componentes
            $this->dispatch('command-created', commandId: $command->id);

            // Mostrar mensaje de éxito
            session()->flash('command_message', 'Comanda creada exitosamente!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Pasar los errores de validación al componente
            $this->addError('newCommandName', $e->validator->errors()->first('name'));
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear la comanda: ' . $e->getMessage());
        }
    }

    // Método para refrescar categorías
    public function refreshCategories($categoryId = null)
    {
        // Forzar la recarga de categorías en el próximo render
        $this->dispatch('$refresh');

        if ($categoryId) {
            $this->category_id = $categoryId;
            // También emitir el cambio para sincronizar
            $this->dispatch('category-changed', $categoryId);
        }
    }

    // Método para verificar si una categoría existe
    public function checkCategoryExists()
    {
        if ($this->newCategoryName) {
            $categoryService = app(CategoriesService::class);
            $exists = $categoryService->categoryExists($this->newCategoryName);

            if ($exists) {
                $this->addError('newCategoryName', 'Esta categoría ya existe.');
            } else {
                $this->resetErrorBag('newCategoryName');
            }
        }
    }

    //============COMANDAS========================//
    public function toggleCommandInput()
    {
        $this->showCommandInput = ! $this->showCommandInput;
        if ($this->showCommandInput) {
            $this->resetValidation();
            $this->newCommandName = '';
        }
    }

    public function saveCommand()
    {
        $this->ensureTenantConnection();
        try {
            // Usar el servicio para crear la categoría
            $categoryService = app(CategoriesService::class);
            $category = $categoryService->createCategory([
                'name' => $this->newCommandName,
                'status' => 1,
            ]);

            // Actualizar la lista de categorías y seleccionar la nueva
            $this->category_id = $category->id;

            // Resetear el formulario de categoría
            $this->showCommandInput = false;
            $this->newCommandName = '';

            // Emitir evento para actualizar componentes
            $this->dispatch('category-created', categoryId: $category->id);

            // Mostrar mensaje de éxito
            session()->flash('category_message', 'Categoría creada exitosamente!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Pasar los errores de validación al componente
            $this->addError('newCommandName', $e->validator->errors()->first('name'));
        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear la categoría: ' . $e->getMessage());
        }
    }

    // Método para refrescar categorías
    public function refreshCommands($commandId = null)
    {
        // Forzar la recarga de categorías en el próximo render
        $this->dispatch('$refresh');

        if ($commandId) {
            $this->commandId = $commandId;
            // También emitir el cambio para sincronizar
            $this->dispatch('command-changed', $commandId);
        }
    }

    // Método para verificar si una categoría existe
    public function checkCommandExists()
    {
        if ($this->newCommandName) {
            $commandService = app(CommandsServices::class);
            $exists = $commandService->commandExists($this->newCommandName);

            if ($exists) {
                $this->addError('newCommandName', 'Esta comanda ya existe.');
            } else {
                $this->resetErrorBag('newCommandName');
            }
        }
    }

    //============VALORES ITEMS========================//
    public function toggleValuesForm()
    {
        $this->showValuesSection = true;
        $this->messageValues = '';
    }

    public function SaveValueItem()
    {

        $exitsValue = InvValues::where('itemId', $this->item_id)->where('label', $this->labelValue)->exists();
        if ($exitsValue) {
            $this->temporaryErrorMessage = 'Este Item ya tiene registrado un costo inicial.';
        } else {
            $this->temporaryErrorMessage = null;
            $this->resetErrorBag('labelValue');
            // Validar solo los campos del formulario de valores
            $this->validate([
                'valueItem' => 'required|numeric',
                'typeValue' => 'required|string',
                'labelValue' => 'required|string',
            ]);

            try {
                $this->ensureTenantConnection();
                $invValueService = app(InvValuesService::class);

                $invValueService->createValueItem([
                    'date' => Carbon::now(),
                    'values' => $this->valueItem,
                    'type' => $this->typeValue,
                    'itemId' => $this->item_id, // Usar la propiedad del componente
                    'warehouseId' => $this->warehouseIdValue ?? 0,
                    'label' => $this->labelValue
                ]);

                // Resetear y ocultar el formulario
                $this->reset(['valueItem', 'typeValue', 'labelValue']);
                $this->showValuesSection = false;

                $this->messageValues = 'Valor agregado exitosamente';

                // Refrescar para mostrar el nuevo valor
                $this->dispatch('refreshValues');
            } catch (\Exception $e) {
                session()->flash('error', 'Error al crear el valor: ' . $e->getMessage());
            }
        }
    }

    // Método para limpiar el mensaje temporal
    public function clearTemporaryMessage()
    {
        $this->temporaryErrorMessage = null;
        $this->messageValues = '';
    }

    public function updatedInternalCode($value)
    {
        $this->validateInternalCodeExits();
    }

    public function updatedSku($value)
    {
        $this->validateSkuExits();
    }

    public function validateInternalCodeExits(): void
    {
        if (empty($this->internal_code)) {
            $this->internal_codeExists = false;
            $this->validatingInternal_code = false;
            return;
        }

        $this->validatingInternal_code = true;

        try {
            $this->ensureTenantConnection();
            $query = Items::where('internal_code', $this->internal_code);

            if ($this->item_id) {
                $query->where('id', '!=', $this->item_id);
            }
            $this->internal_codeExists = $query->exists();
        } catch (\Exception $e) {
            // Log error but don't break the form
            Log::error('Error validating internal_code exists', [
                'error' => $e->getMessage(),
                'internal_code' => $this->internal_code
            ]);
            $this->internal_codeExists = false;
        } finally {
            $this->validatingInternal_code = false;
        }
    }

    public function validateSkuExits(): void
    {
        if (empty($this->sku)) {
            $this->skuExists = false;
            $this->validatingSku = false;
            return;
        }

        $this->validatingSku = true;
        try {
            $this->ensureTenantConnection();
            $query = Items::where('sku', $this->sku);

            if ($this->item_id) {
                $query->where('id', '!=', $this->item_id);
            }
            $this->skuExists = $query->exists();
        } catch (\Exception $e) {
            // Log error but don't break the form
            Log::error('Error validating sku exists', [
                'error' => $e->getMessage(),
                'sku' => $this->sku
            ]);
            $this->skuExists = false;
        } finally {
            $this->validatingSku = false;
        }
    }

    public function resetForm()
    {
        $this->item_id = '';
        $this->category_id = '';
        $this->name = '';
        $this->internal_code = '';
        $this->sku = '';
        $this->description = '';
        $this->type = '';
        $this->commandId = '';
        $this->brandId = '';
        $this->houseId = '';
        $this->purchase_unit = '';
        $this->consumption_unit = '';
        $this->generic = 1;
        $this->inv_values = [];
        $this->warehouses = [];
        $this->warehouseIdValue = '';
        $this->locationName = '';
        $this->tax = '';
        $this->disabled = false;
        $this->tempValues = []; // Limpiar valores temporales
        $this->showCategoryInput = false;
        $this->newCategoryName = '';
        $this->showCommandInput = false;
        $this->newCommandName = '';
        $this->showValuesSection = false;
        $this->valueItem = 0;
        $this->typeValue;
        $this->labelValue;
        $this->messageValues = '';
        $this->temporaryErrorMessage;
        $this->showValuesModal = false;
        $this->showProductionSection = false;
        $this->showDimensionSection = false;
        $this->showAccesoriosSection = false;
        $this->internal_codeExists = false;
        $this->validatingInternal_code = false;
        $this->skuExists = false;
        $this->validatingSku = false;
    }

    public function clearValidationErrors()
    {
        $this->resetErrorBag(['internal_code', 'sku']);
        $this->internal_codeExists = false;
        $this->skuExists = false;
    }

    public function validateMerchantType()
    {
        $sessionTenant = $this->getTenantId();
        // Obtener el tenant desde la base de datos usando el ID de sesión
        $tenant = Tenant::find($sessionTenant);
        if ($tenant->merchant_type_id === 5) {
            $this->showCommand = true;
        } else {
            $this->showCommand = false;
        }
    }

    /**
     * Obtener el componente ManageValues
     */
    private function getManageValuesComponent()
    {
        // Buscar el componente ManageValues en la vista
        if (isset($this->_view) && isset($this->_view->slots)) {
            foreach ($this->_view->slots as $slot) {
                if ($slot instanceof ManageValues) {
                    return $slot;
                }
            }
        }
        return null;
    }

    /**
     * Guardar valores nuevos para un item
     */
    private function saveNewValuesForItem($itemId, $newValues)
    {

        try {
            foreach ($newValues as $label => $valueData) {
                // Verificar que no exista ya un valor con esa etiqueta
                $exists = InvValues::where('itemId', $itemId)
                    ->where('label', $label)
                    ->exists();

                if (!$exists) {
                    $this->ensureTenantConnection();
                    InvValues::create([
                        'itemId' => $itemId,
                        'label' => $label,
                        'type' => strtolower($valueData['type']),
                        'values' => $valueData['value'],
                        'date' => Carbon::now(),
                        'warehouseId' => 0,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error al guardar valores del item: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validar que todos los precios requeridos estén presentes en tempValues
     * 
     * @param array $requiredPrices Array con los nombres de precios requeridos
     * @return array Array con los precios que faltan, vacío si están todos presentes
     */
    private function validateRequiredPrices(array $requiredPrices): array
    {
        $missingPrices = [];

        foreach ($requiredPrices as $priceLabel) {
            // Verificar si la etiqueta existe en tempValues y tiene un valor válido
            if (!isset($this->tempValues[$priceLabel]) || $this->tempValues[$priceLabel] === null || $this->tempValues[$priceLabel] === '' || $this->tempValues[$priceLabel] <= 0) {
                $missingPrices[] = $priceLabel;
            }
        }

        return $missingPrices;
    }

    /**
     * Guardar valores temporales para un nuevo item
     */
    private function saveTemporaryValues($itemId)
    {
        try {
            // Mapeo de etiquetas a tipos
            $typeMap = [
                'Costo Inicial' => 'costo',
                'Costo' => 'costo',
                'Precio Base' => 'precio',
                'Precio Regular' => 'precio',
                'Precio Crédito' => 'precio',
            ];

            foreach ($this->tempValues as $label => $value) {
                // Solo guardar si el valor no está vacío
                if ($value !== null && $value !== '' && $value > 0) {
                    $this->ensureTenantConnection();
                    InvValues::create([
                        'itemId' => $itemId,
                        'label' => $label,
                        'type' => $typeMap[$label] ?? 'costo',
                        'values' => (float)$value,
                        'date' => Carbon::now(),
                        'warehouseId' => 0,
                    ]);
                }
            }

            // Limpiar los valores temporales después de guardar
            $this->tempValues = [];
        } catch (\Exception $e) {
            Log::error('Error al guardar valores temporales: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sincronizar item con API de facturación
     * @return array ['success' => bool, 'message' => string]
     */
    private function syncItemWithApi(Items $item): array
    {
        try {
            Log::info('🔄 syncItemWithApi INICIO', ['item_id' => $item->id]);

            // Los items de tipo INSUMO no se sincronizan con Alegra
            if ($item->type === 'INSUMO') {
                Log::info('⏭️ syncItemWithApi OMITIDO - tipo INSUMO no se sincroniza con Alegra', [
                    'item_id' => $item->id,
                    'item_type' => $item->type,
                ]);
                return ['success' => true, 'message' => 'Item tipo INSUMO no requiere sincronización con API'];
            }

            // Verificar que tenemos company_id válido, si no lo tenemos, obtenerlo del tenant
            if (!$this->currentCompanyId) {
                Log::info('🔄 currentCompanyId es null, intentando obtener desde tenant', [
                    'item_id' => $item->id
                ]);

                // Obtener company_id desde el tenant actual
                $tenantId = session('tenant_id');
                if ($tenantId) {
                    $tenant = Tenant::find($tenantId);
                    if ($tenant && $tenant->company_id) {
                        $this->currentCompanyId = $tenant->company_id;
                        Log::info('✅ Company ID obtenido desde tenant', [
                            'item_id' => $item->id,
                            'tenant_id' => $tenantId,
                            'company_id' => $this->currentCompanyId
                        ]);
                    } else {
                        Log::error('❌ Tenant no encontrado o sin company_id', [
                            'item_id' => $item->id,
                            'tenant_id' => $tenantId,
                            'tenant_found' => !is_null($tenant),
                            'tenant_company_id' => $tenant->company_id ?? 'null'
                        ]);
                        return [
                            'success' => false,
                            'message' => 'Error de configuración: Tenant no encontrado o sin company_id válido'
                        ];
                    }
                }

                // Si aún no tenemos currentCompanyId, intentar reinicializar la configuración
                if (!$this->currentCompanyId) {
                    Log::info('🔄 Intentando reinicializar configuración', [
                        'item_id' => $item->id
                    ]);

                    try {
                        $this->initializeCompanyConfiguration();

                        if ($this->currentCompanyId) {
                            Log::info('✅ Configuración reinicializada exitosamente', [
                                'item_id' => $item->id,
                                'current_company_id' => $this->currentCompanyId
                            ]);
                        } else {
                            Log::error('❌ Reinicialización falló - currentCompanyId sigue siendo null', [
                                'item_id' => $item->id
                            ]);
                            return [
                                'success' => false,
                                'message' => 'Error de configuración: No se pudo obtener el ID de la empresa'
                            ];
                        }
                    } catch (\Exception $e) {
                        Log::error('❌ Error en reinicialización de configuración', [
                            'item_id' => $item->id,
                            'error' => $e->getMessage()
                        ]);
                        return [
                            'success' => false,
                            'message' => 'Error de configuración: ' . $e->getMessage()
                        ];
                    }
                }
            }

            // Verificar si facturación electrónica está habilitada
            if (!$this->isElectronicInvoicingEnabled($this->currentCompanyId)) {
                Log::info('⏭️ Facturación electrónica no habilitada - omitiendo sincronización', [
                    'item_id' => $item->id,
                    'company_id' => $this->currentCompanyId
                ]);
                return [
                    'success' => true, // No es un error, simplemente no está habilitado
                    'message' => 'Facturación electrónica no está habilitada para esta empresa'
                ];
            }

            Log::info('✅ Iniciando sincronización de item', [
                'item_id' => $item->id,
                'company_id' => $this->currentCompanyId
            ]);

            // MÉTODO OPTIMIZADO: Usar user_id directamente
            $user = Auth::user();
            if (!$user) {
                Log::error('❌ No hay usuario autenticado para sincronización');
                return [
                    'success' => false,
                    'message' => 'Error de autenticación: No hay usuario logueado'
                ];
            }

            // Obtener configuración optimizada
            $optimizedConfig = DatabaseConfigService::getFacturacionConfigByUser($user->id);
            if (!$optimizedConfig) {
                Log::error('❌ No se encontró configuración de facturación para usuario', [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
                return [
                    'success' => false,
                    'message' => 'Error de configuración: No se encontró configuración de facturación para el usuario actual'
                ];
            }

            // Crear ApiClient con detección automática de proxy
            $apiClient = ApiClient::forConfig($optimizedConfig);

            Log::info('🚀 Usando configuración OPTIMIZADA para Items', [
                'user_id' => $user->id,
                'warehouse_id' => $optimizedConfig['warehouse_id'],
                'source' => $optimizedConfig['source']
            ]);

            // Obtener datos de cnf_taxes para accounting
            $taxData = $this->getTaxAccountingData($item->taxId);

            // LOGGING: Mostrar datos del item que se están usando
            Log::info('📊 Datos del item para sincronización', [
                'item_id' => $item->id,
                'item_data' => [
                    'name' => $item->name,
                    'description' => $item->description,
                    'sku' => $item->sku,
                    'internal_code' => $item->internal_code,
                    'categoryId' => $item->categoryId,
                    'taxId' => $item->taxId,
                    'inventoriable' => $item->inventoriable
                ],
                'tax_data_from_cnf_taxes' => $taxData,
                'price_base' => $this->getPriceBase($item)
            ]);

            // Obtener api_data_id de la categoría (ID de Alegra)
            $categoryAlegraId = null;
            if ($item->categoryId) {
                $category = Category::find($item->categoryId);
                $categoryAlegraId = $category ? $category->api_data_id : null;
            } else {
            }

            // Obtener información del store para inventory
            $principalStore = $this->getPrincipalStore();
            $warehouseApiId = null;

            if ($principalStore && $principalStore->api_data_id) {
                $warehouseApiId = (string)$principalStore->api_data_id;
                Log::info('📦 Store principal con API ID encontrado', [
                    'item_id' => $item->id,
                    'store_id' => $principalStore->id,
                    'store_name' => $principalStore->name,
                    'api_data_id' => $principalStore->api_data_id
                ]);
            } else {
                Log::warning('⚠️ Store principal sin api_data_id - usando warehouse por defecto', [
                    'item_id' => $item->id,
                    'store_id' => $principalStore->id ?? 'null',
                    'store_name' => $principalStore->name ?? 'null'
                ]);
                $warehouseApiId = '1'; // Fallback a warehouse por defecto
            }

            $isUpdate = (bool) $item->api_data_id;

            if ($isUpdate) {
                // Al actualizar solo se manda el costo; no se toca el stock de Alegra
                $inventory = [
                    'unit'         => 'unit',
                    'unitCost'     => $this->getCost($item),
                    'negativeSale' => false,
                ];
            } else {
                // Al crear se incluye el warehouse con stock inicial en 0
                $inventory = [
                    'unit'         => 'unit',
                    'unitCost'     => $this->getCost($item),
                    'negativeSale' => false,
                    'warehouses'   => [
                        [
                            'id'              => $warehouseApiId,
                            'initialQuantity' => 0,
                            'minQuantity'     => 0,
                            'maxQuantity'     => 0,
                        ]
                    ],
                ];
            }
            // Preparar datos para la API según estructura requerida
            $apiData = [
                'inventory' => $item->inventoriable == 1 ? $inventory : null,
                'accounting' => [
                    'inventory' => $taxData['inventoryAccount'] ?? null, // inventoryAccount desde cnf_taxes
                    'inventariablePurchase' => $taxData['inventariablePurchaseAccount'] ?? null // inventariablePurchaseAccount desde cnf_taxes
                ],
                'description' => $item->description ?? '',
                'name' => $item->name,
                'reference' => $item->sku ?? $item->internal_code ?? '',
                'price' => [
                    [
                        'idPriceList' => '019ac5f3-5f72-7440-874c-6e53c92fbfde', // Precio 1 (Base)
                        'price'       => $this->getPriceBase($item),
                    ],
                    [
                        'idPriceList' => '019b8e1a-f3fa-73b3-91d7-03f867191b3c', // Precio 2 (Regular)
                        'price'       => $this->getPriceRegular($item),
                    ],
                    [
                        'idPriceList' => '019b8e1b-ab7b-71da-8c15-cf1e136e06c3', // Precio 3 (Crédito)
                        'price'       => $this->getPriceCredito($item),
                    ],
                ],
                'type' => $item->inventoriable == 1 ? 'product' : 'service',
                'tax' => $item->taxId ? (string)$item->taxId : '0' // Convertir a string
            ];

            // Remover inventory si el item no es inventoriable
            if ($item->inventoriable != 1) {
                unset($apiData['inventory']);
            }

            // LOGGING: Mostrar el JSON que se está generando
            Log::info('📋 JSON generado para API de Items', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'json_data' => $apiData,
                'json_formatted' => json_encode($apiData, JSON_PRETTY_PRINT)
            ]);

            // Sincronizar (crear o actualizar)
            if ($item->api_data_id) {
                Log::info('📝 Actualizando item en API', [
                    'item_id' => $item->id,
                    'api_data_id' => $item->api_data_id
                ]);
                $apiResult = $apiClient->updateItem($item->api_data_id, $apiData);
            } else {
                Log::info('📝 Creando item en API', [
                    'item_id' => $item->id,
                    'name' => $item->name
                ]);
                $apiResult = $apiClient->createItem($apiData);
            }

            if ($apiResult['success']) {
                // Guardar ID de la API si se creó exitosamente
                if (isset($apiResult['data']['id']) && !$item->api_data_id) {
                    $item->update(['api_data_id' => $apiResult['data']['id']]);
                    Log::info('💾 ID de API guardado', [
                        'item_id' => $item->id,
                        'api_data_id' => $apiResult['data']['id']
                    ]);
                }

                Log::info('✅ Item sincronizado exitosamente', [
                    'item_id' => $item->id,
                    'api_response_id' => $apiResult['data']['id'] ?? 'N/A'
                ]);

                return [
                    'success' => true,
                    'message' => 'Item sincronizado exitosamente con la API de facturación'
                ];
            } else {
                $errorMessage = $apiResult['message'] ?? 'Error desconocido en la API';
                Log::error('❌ Error sincronizando item', [
                    'item_id' => $item->id,
                    'api_error' => $errorMessage
                ]);

                return [
                    'success' => false,
                    'message' => 'Error en la API de facturación: ' . $errorMessage
                ];
            }
        } catch (\Exception $e) {
            Log::error('❌ Excepción sincronizando item', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
                'error_type' => 'exception'
            ]);

            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener datos de accounting desde cnf_taxes
     */
    private function getTaxAccountingData(?int $taxId): array
    {
        try {
            if (!$taxId) {
                Log::debug('No taxId proporcionado para accounting');
                return [
                    'inventoryAccount' => null,
                    'inventariablePurchaseAccount' => null,
                    'categoryAccount' => null
                ];
            }

            $tax = CnfTaxes::find($taxId);

            if (!$tax) {
                Log::warning('Tax no encontrado en cnf_taxes', ['tax_id' => $taxId]);
                return [
                    'inventoryAccount' => null,
                    'inventariablePurchaseAccount' => null,
                    'categoryAccount' => null
                ];
            }

            Log::debug('Datos de accounting obtenidos', [
                'tax_id' => $taxId,
                'inventoryAccount' => $tax->inventoryAccount,
                'inventariablePurchaseAccount' => $tax->inventariablePurchaseAccount,
                'categoryAccount' => $tax->categoryAccount
            ]);

            return [
                'inventoryAccount' => $tax->inventoryAccount,
                'inventariablePurchaseAccount' => $tax->inventariablePurchaseAccount,
                'categoryAccount' => $tax->categoryAccount
            ];
        } catch (\Exception $e) {
            Log::error('Error obteniendo datos de cnf_taxes', [
                'tax_id' => $taxId,
                'error' => $e->getMessage()
            ]);
            return [
                'inventoryAccount' => null,
                'inventariablePurchaseAccount' => null,
                'categoryAccount' => null
            ];
        }
    }

    /**
     * Obtener precio base del item desde InvValues
     */
    private function getPriceBase($item): float
    {
        try {
            $priceValue = InvValues::where('itemId', $item->id)
                ->where('label', 'Precio Base')
                ->first();

            return $priceValue ? (float)$priceValue->values : 0.0;
        } catch (\Exception $e) {
            Log::error('Error obteniendo precio base', [
                'item_id' => $item->id,
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    private function getPriceRegular($item): float
    {
        try {
            $priceValue = InvValues::where('itemId', $item->id)
                ->where('label', 'Precio Regular')
                ->first();

            return $priceValue ? (float)$priceValue->values : 0.0;
        } catch (\Exception $e) {
            Log::error('Error obteniendo precio regular', ['item_id' => $item->id, 'error' => $e->getMessage()]);
            return 0.0;
        }
    }

    private function getPriceCredito($item): float
    {
        try {
            $priceValue = InvValues::where('itemId', $item->id)
                ->where('label', 'Precio Crédito')
                ->first();

            return $priceValue ? (float)$priceValue->values : 0.0;
        } catch (\Exception $e) {
            Log::error('Error obteniendo precio crédito', ['item_id' => $item->id, 'error' => $e->getMessage()]);
            return 0.0;
        }
    }

    /**
     * Obtener costo del item desde InvValues
     */
    private function getCost($item): float
    {
        try {
            $costValue = InvValues::where('itemId', $item->id)
                ->where('label', 'Costo')
                ->first();

            return $costValue ? (float)$costValue->values : 0.0;
        } catch (\Exception $e) {
            Log::error('Error obteniendo costo del item', [
                'item_id' => $item->id,
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Obtener el store principal de la empresa
     */
    private function getPrincipalStore(): ?InvStore
    {
        try {
            $this->ensureTenantConnection();

            // Buscar el store principal (puede ser por status = 1 y el primero, o por algún campo específico)
            $principalStore = InvStore::where('status', 1)
                ->orderBy('id', 'asc')
                ->first();

            if (!$principalStore) {
                Log::warning('No se encontró store principal para la empresa', [
                    'company_id' => $this->currentCompanyId
                ]);
                return null;
            }

            Log::info('Store principal encontrado', [
                'store_id' => $principalStore->id,
                'store_name' => $principalStore->name,
                'company_id' => $this->currentCompanyId
            ]);

            return $principalStore;
        } catch (\Exception $e) {
            Log::error('Error obteniendo store principal', [
                'error' => $e->getMessage(),
                'company_id' => $this->currentCompanyId
            ]);
            return null;
        }
    }

    /**
     * Crear registro en inv_items_store para el item (solo si es inventoriable)
     */
    private function createItemStore(Items $item): void
    {
        try {
            // Verificar que el item sea inventoriable antes de continuar
            if ($item->inventoriable != 1) {
                Log::warning('Intento de crear registro en inv_items_store para item no inventoriable', [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'inventoriable' => $item->inventoriable
                ]);
                return;
            }

            $principalStore = $this->getPrincipalStore();

            if (!$principalStore) {
                Log::warning('No se pudo crear registro en inv_items_store: no hay store principal', [
                    'item_id' => $item->id,
                    'item_name' => $item->name
                ]);
                return;
            }

            // Verificar si ya existe el registro
            $existingRecord = InvItemsStore::where('itemId', $item->id)
                ->where('storeId', $principalStore->id)
                ->first();

            if ($existingRecord) {
                Log::info('Registro en inv_items_store ya existe', [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'store_id' => $principalStore->id,
                    'store_name' => $principalStore->name
                ]);
                return;
            }

            // Crear nuevo registro
            InvItemsStore::create([
                'itemId'              => $item->id,
                'storeId'             => $principalStore->id,
                'initial_stock'       => 0,
                'stock_items_store'   => 0,
                'stock_min'           => 0,
                'stock_max'           => 0,
                'wp_stock_percentage' => max(0, min(100, (float) $this->wpStockPercentage)),
                'wp_min_stock'        => max(0, (float) $this->wpMinStock),
            ]);

            Log::info('✅ Registro creado en inv_items_store para item inventoriable', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'store_id' => $principalStore->id,
                'store_name' => $principalStore->name,
                'inventoriable' => $item->inventoriable
            ]);
        } catch (\Exception $e) {
            Log::error('Error creando registro en inv_items_store', [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'inventoriable' => $item->inventoriable,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Verificar si facturación electrónica está habilitada
     */
    private function isElectronicInvoicingEnabled(?int $companyId): bool
    {
        try {
            if (!$companyId) {
                Log::warning('Company ID es null para verificación de facturación electrónica');
                return false;
            }

            // Asegurar que currentCompanyId está configurado
            if ($this->currentCompanyId !== $companyId) {
                Log::warning('⚠️ currentCompanyId diferente al companyId pasado', [
                    'current_company_id' => $this->currentCompanyId,
                    'passed_company_id' => $companyId
                ]);
                // Si no coinciden, usar el companyId pasado como referencia
                $this->currentCompanyId = $companyId;
            }

            $optionValue = $this->getOptionValue(8); // Option ID 8 = facturación electrónica
            $enabled = $optionValue == 1;

            Log::debug('Verificación de facturación electrónica', [
                'company_id' => $companyId,
                'option_value' => $optionValue,
                'enabled' => $enabled
            ]);

            return $enabled;
        } catch (\Exception $e) {
            Log::error('Error verificando facturación electrónica', [
                'company_id' => $companyId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function updatedType($value)
    {
        $this->type = $value;
    }

    public function canUseImports()
    {
        $result = $this->isOptionEnabled(48);
        $value = $this->getOptionValue(48);

        Log::info('🚚 canUseImports() verificación', [
            'companyId' => $this->currentCompanyId,
            'option_id' => 48,
            'result' => $result ? 'TRUE' : 'FALSE',
            'option_value' => $value,
            'configService_exists' => $this->configService ? 'YES' : 'NO',
            'method_called' => 'isOptionEnabled(48) y getOptionValue(48)'
        ]);
        return $result;
    }

    public function showGeneralInfo()
    {
        $this->showProductionSection = false;
        $this->showDimensionSection = false;
        $this->showAccesoriosSection = false;
    }

    public function activateAccesoriosSection(int $item_id): void
    {
        $this->item_id = $item_id;
        $this->showAccesoriosSection = true;
        $this->showProductionSection = false;
        $this->showDimensionSection = false;
    }

    public function showImportSection($item_id)
    {
        $this->item_id = $item_id;
        $this->showProductionSection = true;
        $this->showDimensionSection = false;
        $this->showAccesoriosSection = false;
    }

    public function showProductionSection($item_id)
    {
        Log::info('🏭 showProductionSection llamado', [
            'item_id' => $item_id,
            'type'    => $this->type,
        ]);
        $this->item_id = $item_id;
        $this->showProductionSection = true;
        $this->showDimensionSection = false;
        $this->showAccesoriosSection = false;
    }

    public function  activateDimensionSection($item_id)
    {
        Log::info('📏 showDimensionSection llamado', [
            'item_id' => $item_id,
            'type'    => $this->type,
            'inventoriable' => $this->inventoriable,
        ]);
        $this->item_id = $item_id;
        $this->showDimensionSection = true;
        $this->showProductionSection = false;
        $this->showAccesoriosSection = false;
    }

    public function exportSpecialStocks()
    {
        $this->ensureTenantConnection();

        $data = Items::with(['quarantineMovements', 'showroomMovements', 'brand'])
            ->get()
            ->filter(function($item) {
                return $item->quarantine_stock > 0 || $item->showroom_stock > 0;
            })
            ->sortBy('name');

        $headings = [
            'SKU (Código Interno)',
            'Nombre del Producto',
            'Marca',
            'Cantidad en Cuarentena',
            'Observación Última Cuarentena',
            'Cantidad en Vitrina / Exhibición',
            'Observación Última Vitrina / Exhibición'
        ];

        $mapping = function($item) {
            $lastQuarantine = $item->quarantineMovements->sortByDesc('created_at')->first();
            $lastShowroom = $item->showroomMovements->sortByDesc('created_at')->first();

            return [
                $item->internal_code ?? $item->sku,
                $item->name,
                $item->brand?->name ?? 'N/A',
                $item->quarantine_stock,
                $lastQuarantine ? $lastQuarantine->justification : '',
                $item->showroom_stock,
                $lastShowroom ? $lastShowroom->justification : ''
            ];
        };

        $filename = 'Reporte_Especial_Inventario_' . now()->format('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\GenericExport($data, $headings, $mapping),
            $filename
        );
    }
}
