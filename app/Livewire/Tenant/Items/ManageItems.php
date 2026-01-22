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

class ManageItems extends Component
{

    use WithPagination, HasCompanyConfiguration, WithExport;

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
    public $tax;
    public $disabled = false;
    public $handles_serial;
    public $inventoriable;
    public $tempValues = [];

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

    // tipos disponibles (puedes externalizarlo si lo prefieres)
    public $types = [
        'COMBO' => 'Combo',
        'COMPRA NACIONAL' => 'Compra nacional',
        'IMPORTADO' => 'Importado',
        'PRODUCIDO' => 'Producido',
    ];

    public $allLabelsValues = [
        'costo' => [
            'Costo Inicial' => 'Costo Inicial',
            'Costo' => 'Costo',
        ],
        'precio' => [
            'Precio Base' => 'Precio Base',
            'Precio Regular' => 'Precio Regular',
            'Precio Crédito' => 'Precio Crédito',
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
        'category_id' => 'required',
        'name' => 'required|min:3',
        'type' => 'required',
        'internal_code' => 'nullable|string',
        'brandId' => 'nullable|integer',
        'houseId' => 'nullable|integer',
        'purchase_unit' => 'nullable|integer',
        'consumption_unit' => 'nullable|integer',
    ];

    protected $messages = [
        'category_id.required' => 'La categoría es obligatoria',
        'name.required' => 'El nombre del item es obligatorio',
        'name.min' => 'El nombre del item debe tener al menos 3 caracteres',
        'type.required' => 'El tipo de item es obligatorio',
        'internal_code.required' => 'El código interno es obligatorio',
        'brandId.required' => 'La marca es obligatoria',

    ];


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

        // DEBUG: Log para verificar inicialización
        Log::info('🔍 DetailPettyCash mount() ejecutado', [
            'currentCompanyId' => $this->currentCompanyId,
            'currentPlainId' => $this->currentPlainId,
            'configService_exists' => $this->configService ? 'YES' : 'NO'
        ]);
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
        $this->disabled = true;

        $this->showModal = true;
    }

    public function render()
    {
        $this->ensureTenantConnection();
        $this->loadWarehouses();

        $items = Items::query()
            ->with(['brand', 'principalImage', 'purchasingUnit', 'consumptionUnit', 'tax'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%')
                    ->orWhere('internal_code', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.items.manage-items', [
            'items' => $items,
            'categories' => Category::where('status', 1)->get(),
            'types' => $this->types
        ]);
    }

    public function toggleGeneric()
    {
        $this->generic = $this->generic ? 0 : 1;
    }

    public function create()
    {
        $this->resetExcept(['categories', 'types', 'allLabelsValues']);
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
            'commandId' => $this->commandId ?: null,
            'brandId' => $this->brandId,
            'houseId' => $this->houseId,
            'inventoriable' => $this->inventoriable,
            'purchasing_unit' => $this->purchase_unit,
            'consumption_unit' => $this->consumption_unit,
            'status' => 1,
            'generic' => $this->generic,
            'taxId' => $this->tax,
            'handles_serial' => $this->handles_serial ?? 0,
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
                        $existingRecord = InvItemsStore::where('itemId', $item->id)->first();
                        if (!$existingRecord) {
                            Log::warning('Item inventoriable sin registro en inv_items_store - creando', [
                                'item_id' => $item->id
                            ]);
                            $this->createItemStore($item);
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
                    session()->flash('message', 'Item actualizado correctamente.');

                    // Solo cerrar modal si no hay errores de sincronización
                    if (!session()->has('sync_warning') && !session()->has('sync_error')) {
                        $this->showModal = false; // Close modal after update
                    }
                    $this->resetValidation(); // Clear validation errors for next open
                    $this->resetForm(); // Clear the form completely for next new item
                }
            } else { // New item
                // Validar que los cinco tipos de precios estén registrados
                $requiredPrices = ['Costo Inicial', 'Costo', 'Precio Base', 'Precio Regular', 'Precio Crédito'];
                $missingPrices = $this->validateRequiredPrices($requiredPrices);
                //dd($missingPrices);
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

                    session()->flash('message', 'Item creado correctamente.');

                    // Solo proceder a editar si no hay errores de sincronización
                    if (!session()->has('sync_warning') && !session()->has('sync_error')) {
                        $this->resetValidation(); // Clear validation for current submission
                        $this->edit($item_id); // Load new item into the form (sets showModal=true, disabled=true)
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
        return Items::query()
            ->with(['brand', 'tax', 'purchasingUnit', 'consumptionUnit', 'invItemsStore'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%')
                    ->orWhere('internal_code', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();
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
            'Estado'
        ];
    }

    public function getExportMapping($item): array
    {
        $stock = 'No maneja';
        if ($item->inventoriable == 1) {
            if ($item->invItemsStore->isNotEmpty()) {
                $stocks = [];
                foreach ($item->invItemsStore as $store) {
                    $stocks[] = $store->stock_items_store;
                }
                $stock = implode(' / ', $stocks);
            } else {
                $stock = '0';
            }
        }

        return [
            $item->sku,
            $item->internal_code ?? $item->internalCode ?? '',
            $item->name,
            $item->type,
            $item->brand->name ?? 'SIN MARCA',
            $stock,
            $item->purchasingUnit->description ?? 'N/A',
            $item->consumptionUnit->description ?? 'N/A',
            $item->tax->name ?? 'Sin impuesto',
            $item->status ? 'Activo' : 'Inactivo'
        ];
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
        $this->ensureTenantConnection();

        $centralDbName = config('database.connections.central.database');
        $userId = Auth::id(); // Get the authenticated user's ID

        $exists = DB::table("{$centralDbName}.users", 'u')
            ->join("{$centralDbName}.usr_profile_merchant as upm", 'upm.profile_id', '=', 'u.profile_id')
            ->where('u.id', $userId)
            ->where('upm.merchant_type_id', 5)
            ->exists(); // Check if any record exists

        $this->showCommand = $exists; // Set showCommand based on the existence check
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

            // Crear ApiClient con configuración optimizada
            $apiClient = new ApiClient(
                $optimizedConfig['base_url'],
                $optimizedConfig['token'],
                $optimizedConfig['username'],
                $optimizedConfig['timeout']
            );

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

            $inventory = [
                'unit' => 'unit',
                'unitCost' => $this->getCost($item),
                'negativeSale' => false,
                'warehouses' => [
                    [
                        'id' => $warehouseApiId,
                        'initialQuantity' => 0,
                        'minQuantity' => 0,
                        'maxQuantity' => 0
                    ]
                ]
            ];
            // Preparar datos para la API según estructura requerida
            $apiData = [
                'itemCategory' => [
                    'id' => $categoryAlegraId // api_data_id de la categoría (ID de Alegra)
                ],
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
                        'price' => $this->getPriceBase($item) // Obtener precio base
                    ]
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
                'itemId' => $item->id,
                'storeId' => $principalStore->id,
                'initial_stock' => 0,
                'stock_items_store' => 0,
                'stock_min' => 0,
                'stock_max' => 0,
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
}
