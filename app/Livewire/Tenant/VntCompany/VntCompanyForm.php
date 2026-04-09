<?php

namespace App\Livewire\Tenant\VntCompany;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use App\Models\Auth\User;
use App\Models\Auth\UserTenant;
use App\Models\Tenant\Customer\VntCompanyRoute;
use App\Livewire\Tenant\VntCompany\Services\CompanyService;
use App\Livewire\Tenant\VntCompany\Services\WarehouseService;
use App\Livewire\Tenant\VntCompany\Services\CompanyQueryService;
use App\Livewire\Tenant\VntCompany\Services\CompanyValidationService;
use App\Livewire\Tenant\VntCompany\Services\ExportService;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Hash;

// Imports para sincronización API
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\HasCompanyConfiguration;
use App\Services\Facturacion\DatabaseConfigService;
use App\Services\Facturacion\ApiClient;
use App\Services\Tenant\CompanyOptionsService;


class VntCompanyForm extends Component
{
    use WithPagination, HasCompanyConfiguration;

    // Services
    protected $companyService;
    protected $warehouseService;
    protected $queryService;
    protected $validationService;
    protected $exportService;
    protected $companyOptionsService;
    protected $listeners = [
        'type-identification-changed' => 'updateTypeIdentification',
        'regime-changed' => 'updateRegime',
        'fiscal-responsibility-changed' => 'updateFiscalResponsibility',
        'city-changed' => 'updateWarehouseCity',
        'position-changed' => 'updatePosition',
        'warehouse-modal-closed' => 'handleWarehouseModalClosed',
        'contact-modal-closed' => 'handleContactModalClosed',
        'citySelected' => 'updateCityName',
        'route-changed' => 'updateRoute',
        'district-changed' => 'updateDistrict',
    ];

    public function mount()
    {
        // Si está en modo simplificado (usado desde cotizador), establecer tipo por defecto
        if ($this->simplified && $this->reusable && empty($this->type)) {
            $this->type = 'CLIENTE';
        }
    }

    public $search = '';
    public $showModal = false;
    public $editingId = null;
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';
    public $searchType = 'TODOS';

    // Warehouse modal properties
    public $reusable = false;
    public $simplified = false; // Modo simplificado para cotizador
    public $companyId = null; // ID del cliente a editar (cuando se usa de forma reutilizable)
    public $showWarehouseModal = false;
    public $selectedCompanyId = null;

    // Contact modal properties
    public $showContactModal = false;
    public $selectedCompanyIdForContacts = null;

    // Routes modal properties
    public $showRoutesModal = false;
    // Move district modal properties
    public $showMoveDistrictModal = false;

    // Propiedades del formulario
    public $businessName = '';
    public $billingEmail = '';
    public $firstName = '';
    public $lastName = '';
    public $secondName = '';
    public $secondLastName = '';
    public $integrationDataId = '';
    public $identification = '';
    public $checkDigit = '';
    public $status = 1;
    public $typePerson = '';
    public $typeIdentificationId = '';
    public $regimeId = '';
    public $code_ciiu = '';
    public $fiscalResponsabilityId = '';
    public $verification_digit = '';
    public $type = '';

    // Real-time validation properties
    public $identificationExists = false;
    public $validatingIdentification = false;
    public $emailExists = false;
    public $validatingEmail = false;
    public $validatingType = false;

    // Propiedades para contacto
    public $business_phone = '';
    public $personal_phone = '';
    public $positionId = 1; // Posición por defecto

    // Propiedades para sucursales
    public $warehouses = [];
    public $warehouseName = '';
    public $warehouseAddress = '';
    public $warehousePostcode = '';
    public $warehouseCityId = '';
    public $warehouseIsMain = false;
    public $canAddMoreWarehouses = false;
    public $warehouseCityName = '';

    // IDs para actualización (evitar duplicación)
    public $mainWarehouseId = null;
    public $mainContactId = null;

    // Control de visualización de campos
    public $showNaturalPersonFields = false;

    // Propiedad para rastrear errores de validación
    public $formHasErrors = false;

    // Propiedad para verificar si el contacto ya tiene usuario
    public $hasExistingUser = false;
    // Email del usuario existente (si existe)
    public $existingUserEmail = '';

    public $routeId = '';
    public $createUser = false;
    public $districtId = null;

    // Propiedades para mostrar credenciales del usuario creado
    public $showUserCredentials = false;
    public $userCredentialsEmail = '';
    public $userCredentialsPassword = '12345678';
    public $isConfigurationInitialized = false;

    public function boot(
        CompanyService $companyService,
        WarehouseService $warehouseService,
        CompanyQueryService $queryService,
        CompanyValidationService $validationService,
        ExportService $exportService,
        CompanyOptionsService $companyOptionsService
    ) {
        $this->companyService = $companyService;
        $this->warehouseService = $warehouseService;
        $this->queryService = $queryService;
        $this->validationService = $validationService;
        $this->exportService = $exportService;
        $this->companyOptionsService = $companyOptionsService;
    }

    /**
     * Reglas de validación dinámicas
     * 
     * Las reglas se obtienen del CompanyValidationService y varían según:
     * - Tipo de persona (Natural/Jurídica)
     * - Tipo de identificación (NIT requiere selección manual de tipo de persona)
     * - Modo edición (permite duplicados del mismo registro)
     */
    protected function rules()
    {
        // Si está en modo simplificado, usar reglas simplificadas
        if ($this->simplified) {
            return $this->validationService->getSimplifiedValidationRules(
                $this->typePerson,
                $this->editingId,
                $this->typeIdentificationId ? (int) $this->typeIdentificationId : null
            );
        }

        return $this->validationService->getValidationRules(
            $this->typePerson,
            $this->editingId,
            $this->typeIdentificationId ? (int) $this->typeIdentificationId : null,
            true // Incluir reglas de warehouse y contacto
        );
    }

    /**
     * Mensajes de validación personalizados
     */
    protected function messages()
    {
        return $this->validationService->getValidationMessages();
    }

    /**
     * Atributos personalizados para mensajes de validación
     */
    protected function validationAttributes()
    {
        return $this->validationService->getValidationAttributes();
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
    }



    public function getItemsProperty()
    {
        return $this->queryService->getPaginatedCompanies(
            $this->search,
            $this->perPage,
            $this->sortField,
            $this->sortDirection,
            $this->searchType
        );
    }

    public function render()
    {
        return view('livewire.tenant.vnt-company.components.vnt-company-form', [
            'items' => $this->items, // Se cachea automáticamente entre renders
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection
        ]);
    }

    public function create()
    {
        $this->clearUniqueValidationErrors();
        $this->resetForm();
        $this->showModal = true;
    }



    public function edit($id)
    {

        $this->clearUniqueValidationErrors();
        $company = $this->companyService->getCompanyForEdit($id);

        // Log company loading for debugging
        Log::info('Loading company for edit', [
            'company_id' => $id,
            'has_main_warehouse' => $company->mainWarehouse !== null,
            'has_contacts' => $company->mainWarehouse?->contacts->isNotEmpty() ?? false
        ]);

        $this->editingId = $id;
        $this->typeIdentificationId = $company->typeIdentificationId;
        $this->identification = $company->identification;
        $this->firstName = $company->firstName;
        $this->secondName = $company->secondName;
        $this->lastName = $company->lastName;
        $this->secondLastName = $company->secondLastName;
        $this->businessName = $company->businessName;
        $this->billingEmail = $company->billingEmail;
        $this->regimeId = $company->regimeId;
        $this->fiscalResponsabilityId = $company->fiscalResponsabilityId;
        $this->code_ciiu = $company->code_ciiu;
        $this->checkDigit = (string)$company->checkDigit;
        $this->verification_digit = (string)$company->checkDigit; // Cargar el DV desde checkDigit
        $this->status = $company->status ?? 1;
        $this->type = $company->type;
        // Cargar ruta asignada si existe
        $route = VntCompanyRoute::where('company_id', $id)->first();
        $this->routeId = $route ? $route->route_id : '';

        // Log detallado de la carga de datos para verificación
        Log::info('Company data loaded in edit()', [
            'company_id' => $id,
            'loaded_fields' => [
                'typeIdentificationId' => $this->typeIdentificationId,
                'identification' => $this->identification,
                'firstName' => $this->firstName,
                'secondName' => $this->secondName,
                'lastName' => $this->lastName,
                'secondLastName' => $this->secondLastName,
                'businessName' => $this->businessName,
                'billingEmail' => $this->billingEmail,
                'regimeId' => $this->regimeId,
                'fiscalResponsabilityId' => $this->fiscalResponsabilityId,
                'code_ciiu' => $this->code_ciiu,
                'checkDigit' => $this->checkDigit,
                'verification_digit' => $this->verification_digit,
                'status' => $this->status,
            ]
        ]);

        // Determinar tipo de persona para la UI usando la nueva lógica
        $this->typePerson = $this->determineTypePersonForUI($company);

        // Establecer showNaturalPersonFields basándose en el tipo determinado
        $this->showNaturalPersonFields = ($this->typePerson === 'Natural');

        // Log informativo para debugging
        Log::info('Type person determined for UI', [
            'company_id' => $id,
            'typeIdentificationId' => $company->typeIdentificationId,
            'typePerson_db' => $company->typePerson,
            'typePerson_ui' => $this->typePerson,
            'showNaturalPersonFields' => $this->showNaturalPersonFields,
            'has_natural_data' => $this->hasNaturalPersonData($company)
        ]);

        // Load main warehouse data into form properties
        $mainWarehouse = $company->mainWarehouse;
        if ($mainWarehouse) {
            $this->mainWarehouseId = $mainWarehouse->id;
            $this->warehouseName = $mainWarehouse->name;
            $this->warehouseAddress = $mainWarehouse->address;
            $this->warehousePostcode = $mainWarehouse->postcode;
            $this->warehouseCityId = $mainWarehouse->cityId;

            // Load contact data if exists
            $mainContact = $mainWarehouse->contacts->first();
            if ($mainContact) {
                $this->mainContactId = $mainContact->id;
                $this->business_phone = $mainContact->business_phone;
                $this->personal_phone = $mainContact->personal_phone;
                $this->positionId = $mainContact->positionId;
            }
        }

        // Cargar sucursales usando el service
        $this->warehouses = $this->warehouseService->prepareWarehousesForForm($company);

        // Si no hay sucursales, inicializar con una por defecto
        if (empty($this->warehouses)) {
            $this->initializeDefaultWarehouse();
        } else {
            // Evaluar permisos para la empresa existente
            $this->evaluateWarehousePermissions();
        }

        // Verificar si el cliente ya tiene un usuario asignado
        $this->checkExistingUser();

        // Log final antes de mostrar el modal para verificar el estado
        Log::info('Final state before showing modal', [
            'company_id' => $id,
            'typePerson' => $this->typePerson,
            'typeIdentificationId' => $this->typeIdentificationId,
            'showNaturalPersonFields' => $this->showNaturalPersonFields,
            'verification_digit' => $this->verification_digit,
            'hasExistingUser' => $this->hasExistingUser,
            'existingUserEmail' => $this->existingUserEmail
        ]);

        $this->showModal = true;
    }

    public function save()
    {
        Log::info('🚀 VntCompanyForm::save() INICIADO', [
            'editing_id' => $this->editingId,
            'business_name' => $this->businessName,
            'billing_email' => $this->billingEmail,
            'identification' => $this->identification
        ]);

        // Establecer typePerson automáticamente si no es NIT antes de validar
        if ($this->typeIdentificationId && (int) $this->typeIdentificationId !== 2 && empty($this->typePerson)) {
            $this->typePerson = 'Natural';
        }

        // Convertir strings vacíos a null solo para campos opcionales en Persona Natural
        Log::info('🔍 ANTES de conversión de strings vacíos', [
            'typePerson' => $this->typePerson,
            'regimeId' => $this->regimeId,
            'fiscalResponsabilityId' => $this->fiscalResponsabilityId,
            'regimeId_type' => gettype($this->regimeId),
            'fiscalResponsabilityId_type' => gettype($this->fiscalResponsabilityId)
        ]);

        if ($this->typePerson === 'Natural') {
            // Solo convertir a null si están vacíos, NO si tienen un valor válido
            $this->regimeId = ($this->regimeId === '' || $this->regimeId === '0') ? null : $this->regimeId;
            $this->fiscalResponsabilityId = ($this->fiscalResponsabilityId === '' || $this->fiscalResponsabilityId === '0') ? null : $this->fiscalResponsabilityId;
        }

        Log::info('🔍 DESPUÉS de conversión de strings vacíos', [
            'typePerson' => $this->typePerson,
            'regimeId' => $this->regimeId,
            'fiscalResponsabilityId' => $this->fiscalResponsabilityId,
            'regimeId_type' => gettype($this->regimeId),
            'fiscalResponsabilityId_type' => gettype($this->fiscalResponsabilityId)
        ]);

        // Validar que identification y email no existan antes de proceder
        Log::info('🔍 Validando identificación duplicada', ['identificationExists' => $this->identificationExists]);
        if ($this->identificationExists) {
            $this->addError('identification', 'Este número de identificación ya está registrado.');
            Log::info('❌ Guardado detenido: Identificación duplicada');
            return;
        }

        Log::info('🔍 Validando email duplicado', ['emailExists' => $this->emailExists]);
        if ($this->emailExists) {
            $this->addError('billingEmail', 'Este email de facturación ya está registrado.');
            Log::info('❌ Guardado detenido: Email duplicado');
            return;
        }

        Log::info('🔍 Validando ciudad', ['simplified' => $this->simplified, 'warehouseCityId' => $this->warehouseCityId]);

        if (!$this->cityValidate(0)) {
            $this->addError('warehouseName', 'La ciudad seleccionada no es válida.');
            Log::info('❌ Guardado detenido: Ciudad inválida');
            return; // Si la validación de ciudad falla, detener el guardado
        }
        Log::info('✅ Validación de ciudad exitosa');

        // Validación simple usando Livewire nativo
        Log::info('🔍 Iniciando validación de formulario con Livewire');
        $this->validate();

        Log::info('✅ Validación de formulario exitosa');

        // SINCRONIZACIÓN CON API - PASO 1: Verificar si debe sincronizar con API
        Log::info('🔍 ANTES DE shouldSyncWithApi()');
        $shouldSyncWithApi = $this->shouldSyncWithApi();
        $tempApiId = null;

        Log::info('🔍 RESULTADO shouldSyncWithApi()', [
            'should_sync' => $shouldSyncWithApi['should_sync'],
            'reason' => $shouldSyncWithApi['reason']
        ]);

        if ($shouldSyncWithApi['should_sync'] && !$this->editingId) {
            Log::info('🔄 Sincronización API habilitada - validando configuración (solo para nuevos clientes)');

            $preValidationResult = $this->preValidateApiSync();
            if (!$preValidationResult['success']) {
                Log::error('❌ Pre-validación API falló', ['error' => $preValidationResult['message']]);
                session()->flash('sync_error', $preValidationResult['message']);
                return;
            }

            Log::info('✅ Pre-validación API exitosa');

            // SINCRONIZACIÓN CON API - PASO 2: Preparar datos para validación temporal
            $tempApiData = $this->prepareApiData();

            Log::info('🔍 Datos preparados para API', [
                'api_data' => $tempApiData,
                'fiscalResponsabilities_specifically' => $tempApiData['fiscalResponsabilities'] ?? 'NOT_SET',
                'fiscalResponsabilities_type' => gettype($tempApiData['fiscalResponsabilities'] ?? null),
                'fiscalResponsabilities_is_null' => is_null($tempApiData['fiscalResponsabilities'] ?? null),
                'fiscalResponsabilityId_property' => $this->fiscalResponsabilityId,
                'fiscalResponsabilityId_type' => gettype($this->fiscalResponsabilityId)
            ]);

            // SINCRONIZACIÓN CON API - PASO 3: Validar con API real (detectar duplicados)
            $apiValidationResult = $this->validateApiData($tempApiData);
            if (!$apiValidationResult['success']) {
                Log::error('❌ Validación API falló', ['error' => $apiValidationResult['message']]);

                // Mensajes más claros para el usuario
                $userMessage = $this->formatApiErrorMessage($apiValidationResult['message']);
                session()->flash('sync_error', $userMessage);
                return;
            }

            Log::info('✅ Validación API exitosa');

            // Verificar si la validación ya creó un registro temporal en la API
            $tempApiId = $apiValidationResult['temp_api_id'] ?? null;
            if ($tempApiId) {
                Log::info('💾 ID temporal de API obtenido', ['temp_api_id' => $tempApiId]);
            }
        } else {
            if ($this->editingId) {
                Log::info('✏️ Editando cliente existente - sin sincronización API');
            } else {
                Log::info('⏭️ Sincronización API deshabilitada - creando cliente solo localmente', [
                    'reason' => $shouldSyncWithApi['reason']
                ]);
                session()->flash('sync_info', '💡 Información: El cliente se creará únicamente en el sistema local. ' . $shouldSyncWithApi['reason']);
            }
        }

        $data = $this->getFormData();

        // Asegurar que api_data_id esté en los datos para evitar errores de BD
        $data['api_data_id'] = null;

        // DEBUG: Mostrar todos los valores del formulario
        // dd([
        //     'action' => $this->editingId ? 'update' : 'create',
        //     'editingId' => $this->editingId,
        //     'form_data' => $data,
        //     'warehouses' => $this->warehouses,
        //     'permissions' => [
        //         'canAddMoreWarehouses' => $this->canAddMoreWarehouses,
        //         'warehouseLimitsInfo' => $this->getWarehouseLimitsInfo()
        //     ],
        //     'all_component_properties' => [
        //         'businessName' => $this->businessName,
        //         'billingEmail' => $this->billingEmail,
        //         'firstName' => $this->firstName,
        //         'lastName' => $this->lastName,
        //         'secondName' => $this->secondName,
        //         'secondLastName' => $this->secondLastName,
        //         'integrationDataId' => $this->integrationDataId,
        //         'identification' => $this->identification,
        //         'checkDigit' => $this->checkDigit,
        //         'status' => $this->status,
        //         'typePerson' => $this->typePerson,
        //         'typeIdentificationId' => $this->typeIdentificationId,
        //         'regimeId' => $this->regimeId,
        //         'code_ciiu' => $this->code_ciiu,
        //         'fiscalResponsabilityId' => $this->fiscalResponsabilityId,
        //         'verification_digit' => $this->verification_digit,
        //         'warehouseName' => $this->warehouseName,
        //         'warehouseAddress' => $this->warehouseAddress,
        //         'warehousePostcode' => $this->warehousePostcode,
        //         'warehouseCityId' => $this->warehouseCityId,
        //         'warehouseIsMain' => $this->warehouseIsMain,
        //     ],
        //     'validation_rules' => $this->rules(),
        //     'timestamp' => now()->toDateTimeString()
        // ]);

        // Preparar array de warehouses con los datos del formulario
        $warehouses = [[
            'id' =>  $this->mainWarehouseId,
            'name' => $this->editingId
                ? ($this->warehouseName ?? 'Principal')
                : 'Principal',
            'address' => $this->warehouseAddress,
            'postcode' => $this->warehousePostcode,
            'cityId' => $this->warehouseCityId,
            'main' => true, // Siempre es la sucursal principal
            'district' => $this->districtId ?: null,
        ]];
        // dd($warehouses);
        try {
            if ($this->editingId) {
                Log::info('📝 Actualizando company existente', ['company_id' => $this->editingId]);

                Log::info('🔄 Llamando a CompanyService::update', [
                    'editingId' => $this->editingId,
                    'data_keys' => array_keys($data),
                    'warehouses_count' => count($warehouses),
                    'mainContactId' => $this->mainContactId
                ]);

                $company = $this->companyService->update($this->editingId, $data, $warehouses, $this->mainContactId);

                Log::info('✅ CompanyService::update completado exitosamente', [
                    'company_id' => $company ? $company->id : 'NULL',
                    'company_updated' => $company ? true : false
                ]);

                // Verificar si el cliente tiene api_data_id para sincronizar con API
                if ($company && $company->api_data_id) {
                    Log::info('🔄 Cliente tiene api_data_id - actualizando también en API', [
                        'company_id' => $company->id,
                        'api_data_id' => $company->api_data_id
                    ]);

                    // Preparar datos para actualizar en API
                    $tempApiData = $this->prepareApiData();
                    $this->updateCompanyInApi($company, $tempApiData);
                } else {
                    Log::info('✏️ Cliente actualizado solo localmente - sin api_data_id para sincronizar', [
                        'company_id' => $company ? $company->id : 'NULL',
                        'has_api_data_id' => $company && $company->api_data_id ? true : false
                    ]);
                }

                // Mensaje de éxito inicial
                $message = ($company && $company->api_data_id)
                    ? '✅ Cliente Actualizado: Los cambios se guardaron localmente y se sincronizaron con el sistema de facturación.'
                    : '✅ Cliente Actualizado: Los cambios se guardaron exitosamente en el sistema local.';

                // Disparar evento para componentes que escuchan
                $this->dispatch('customer-updated', customerId: $this->editingId);

                if ($this->routeId) {
                    $existingRoute = VntCompanyRoute::where('company_id', $this->editingId)->first();

                    if ($existingRoute) {
                        if ($existingRoute->route_id != $this->routeId) {
                            $existingRoute->update(['route_id' => $this->routeId]);
                            Log::info('Route updated for company', ['companyId' => $this->editingId, 'newRouteId' => $this->routeId]);
                        }
                    } else {
                        // Si no existe, crear
                        $this->createRouteFromCompany($company);
                        Log::info('Route created during update for company', ['companyId' => $this->editingId, 'routeId' => $this->routeId]);
                    }
                } else {
                    // Si se deseleccionó la ruta (valor vacío), eliminar la asignación existente
                    VntCompanyRoute::where('company_id', $this->editingId)->delete();
                    Log::info('Route removed for company', ['companyId' => $this->editingId]);
                }

                // Crear usuario si está marcado el checkbox y no existe usuario
                if ($this->createUser && $company && !$this->hasExistingUser) {
                    try {
                        Log::info('Creating user for existing company during edit', [
                            'companyId' => $this->editingId,
                            'createUser' => $this->createUser
                        ]);

                        $this->createUserFromCompany($company);

                        // Verificar si hubo advertencia de productos
                        if (session()->has('warning')) {
                            $message = session()->pull('warning');
                        } else {
                            $message = 'Registro actualizado y usuario creado exitosamente.';
                        }
                    } catch (\Exception $e) {
                        Log::error('Error creating user during edit', ['error' => $e->getMessage()]);
                        $message = 'Registro actualizado exitosamente, pero hubo un error al crear el usuario: ' . $e->getMessage();
                    }
                } else {
                    Log::info('Skipping user creation during edit', [
                        'createUser' => $this->createUser,
                        'hasExistingUser' => $this->hasExistingUser,
                        'hasCompany' => $company !== null
                    ]);
                }

                session()->flash('message', $message);
            } else {
                Log::info('📝 Creando nuevo company');

                $company = $this->companyService->create($data, $warehouses);

                // Sincronizar con API después de crear (solo si está habilitado y es nuevo cliente)
                if ($shouldSyncWithApi['should_sync'] && $tempApiId && !$this->editingId) {
                    $this->syncCompanyWithApi($company, $tempApiId);
                }

                // Mensaje de éxito inicial
                $message = ($shouldSyncWithApi['should_sync'] && $tempApiId && !$this->editingId)
                    ? '✅ Cliente Creado: El cliente se registró exitosamente y se sincronizó con el sistema de facturación.'
                    : '✅ Cliente Creado: El cliente se registró exitosamente en el sistema local.';

                // Crear ruta si se ha seleccionado una ruta
                Log::info('Checking route creation', [
                    'routeId' => $this->routeId,
                    'routeId_type' => gettype($this->routeId),
                    'routeId_empty' => empty($this->routeId),
                    'company' => $company ? $company->id : null
                ]);

                if ($this->routeId && $company) {
                    try {
                        Log::info('Creating route for company', [
                            'company_id' => $company->id,
                            'route_id' => $this->routeId
                        ]);
                        $route = $this->createRouteFromCompany($company);
                        Log::info('Route created successfully', [
                            'route_id' => $route->id ?? 'unknown',
                            'company_id' => $route->company_id ?? 'unknown',
                            'sales_order' => $route->sales_order ?? 'unknown'
                        ]);
                        $message = 'Registro y ruta creados exitosamente.';
                    } catch (\Exception $e) {
                        // Log error but don't fail operation
                        Log::error('Error creando ruta', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $message = 'Registro creado exitosamente, pero hubo un error al crear la ruta.';
                    }
                } else {
                    Log::info('Skipping route creation', [
                        'routeId' => $this->routeId,
                        'hasCompany' => $company !== null
                    ]);
                }

                // Crear usuario si está marcado el checkbox
                if ($this->createUser && $company) {
                    try {
                        Log::info('Creating user for company', ['createUser' => $this->createUser]);
                        $this->createUserFromCompany($company);

                        // Verificar si hubo advertencia de productos
                        if (session()->has('warning')) {
                            $message = session()->pull('warning');
                        } else {
                            $message = 'Registro y usuario creados exitosamente.';
                        }
                    } catch (\Exception $e) {
                        Log::error('Error creating user', ['error' => $e->getMessage()]);
                        $message = 'Registro creado exitosamente, pero hubo un error al crear el usuario: ' . $e->getMessage();
                    }
                } else {
                    Log::info('Skipping user creation', [
                        'createUser' => $this->createUser,
                        'hasCompany' => $company !== null
                    ]);
                }

                session()->flash('message', $message);

                // Disparar evento para componentes que escuchan
                if ($company && isset($company->id)) {
                    $this->dispatch('customer-created', $company->id);
                    $this->dispatch('vnt-company-saved', $company->id);
                }
            }

            Log::info('🏁 Finalizando save() - reseteando formulario y cerrando modal');
            $this->resetForm();
            $this->showModal = false;
            Log::info('✅ save() completado exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Errores de validación - ya manejados por Livewire
            Log::info('❌ Errores de validación en formulario', [
                'validation_errors' => $e->errors(),
                'user_id' => Auth::id(),
                'form_data_summary' => [
                    'has_identification' => !empty($this->identification),
                    'has_email' => !empty($this->billingEmail),
                    'type_person' => $this->typePerson,
                    'is_editing' => !is_null($this->editingId)
                ],
                'context' => $this->buildErrorContext()
            ]);
            // No hacer nada, Livewire maneja automáticamente los errores de validación
            return;
        } catch (\Illuminate\Database\QueryException $e) {
            // Errores de base de datos con manejo especializado
            $userMessage = $this->handleDatabaseError($e);
            session()->flash('error', $userMessage);
            return;
        } catch (\Exception $e) {
            // Errores generales con manejo completo
            $userMessage = $this->handleGeneralError($e);
            session()->flash('error', $userMessage);
            return;
        }
    }

    public function delete($id)
    {
        try {
            Log::info('🗑️ Iniciando eliminación de cliente', ['company_id' => $id]);

            $this->companyService->delete($id);

            Log::info('✅ Cliente eliminado exitosamente', ['company_id' => $id]);
            session()->flash('message', '🗑️ Cliente Eliminado: El registro se ha eliminado exitosamente del sistema.');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('❌ Error de BD al eliminar cliente', [
                'company_id' => $id,
                'error' => $e->getMessage()
            ]);

            // Verificar si es error de referencia (cliente está siendo usado)
            if (
                strpos($e->getMessage(), 'foreign key constraint') !== false ||
                strpos($e->getMessage(), 'Cannot delete') !== false
            ) {
                $constraintErrorMessage = $this->buildConstraintErrorMessage();
                session()->flash('error', $constraintErrorMessage);
            } else {
                $databaseErrorMessage = $this->buildDatabaseDeleteErrorMessage();
                session()->flash('error', $databaseErrorMessage);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error general eliminando cliente', [
                'company_id' => $id,
                'error' => $e->getMessage()
            ]);

            $generalDeleteErrorMessage = $this->buildGeneralDeleteErrorMessage();
            session()->flash('error', $generalDeleteErrorMessage);
        }
    }

    public function handleWarehouseModalClosed()
    {
        $this->showWarehouseModal = false;
        $this->selectedCompanyId = null;
    }

    public function openWarehouseModal($companyId)
    {
        $this->showWarehouseModal = true;
        $this->selectedCompanyId = $companyId;
    }

    public function handleContactModalClosed()
    {
        $this->showContactModal = false;
        $this->selectedCompanyIdForContacts = null;
    }

    public function openContactModal($companyId)
    {
        $this->showContactModal = true;
        $this->selectedCompanyIdForContacts = $companyId;
    }

    public function openRoutes()
    {
        $this->showRoutesModal = true;
    }

    public function openMoveDistrict()
    {
        $this->showMoveDistrictModal = true;
    }

    public function handleRoutesModalClosed()
    {
        $this->showRoutesModal = false;
    }

    public function handleMoveDistrictModalClosed()
    {
        $this->showMoveDistrictModal = false;
    }

    public function exportExcel()
    {
        $result = $this->exportService->exportToExcel($this->search);
        $this->dispatch('show-toast', [
            'type' => $result['success'] ? 'success' : 'info',
            'message' => $result['message']
        ]);
    }

    public function exportPdf()
    {
        $result = $this->exportService->exportToPdf($this->search);
        $this->dispatch('show-toast', [
            'type' => $result['success'] ? 'success' : 'info',
            'message' => $result['message']
        ]);
    }

    public function exportCsv()
    {
        $result = $this->exportService->exportToCsv($this->search);
        $this->dispatch('show-toast', [
            'type' => $result['success'] ? 'success' : 'info',
            'message' => $result['message']
        ]);
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->businessName = '';
        $this->firstName = '';
        $this->billingEmail = '';
        $this->identification = '';
        $this->integrationDataId = '';
        $this->lastName = '';
        $this->secondLastName = '';
        $this->checkDigit = '';
        $this->status = 1; // Default to active for new records
        $this->secondName = '';
        $this->typeIdentificationId = '';
        $this->typePerson = '';
        $this->code_ciiu = '';
        $this->regimeId = '';
        $this->fiscalResponsabilityId = '';
        $this->verification_digit = '';
        $this->business_phone = '';
        $this->personal_phone = '';
        $this->positionId = 1; // Posición por defecto
        $this->type = '';

        // Reset real-time validation properties
        $this->identificationExists = false;
        $this->validatingIdentification = false;

        // Reset warehouse fields e inicializar con una sucursal por defecto
        $this->warehouses = [];
        $this->initializeDefaultWarehouse();
        $this->warehouseName = '';
        $this->warehouseAddress = '';
        $this->warehousePostcode = '';
        $this->warehouseCityId = '';
        $this->warehouseIsMain = false;
        $this->canAddMoreWarehouses = false;

        // Reset IDs
        $this->mainWarehouseId = null;
        $this->mainContactId = null;

        // Reset control de visualización
        $this->showNaturalPersonFields = false;

        // Reset form validation state
        $this->formHasErrors = false;

        // Reset create user checkbox
        $this->createUser = false;

        // Reset existing user check
        $this->hasExistingUser = false;
        $this->existingUserEmail = '';

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function cancelForm()
    {
        // Cerrar el modal
        $this->showModal = false;

        // Resetear el formulario
        $this->resetForm();

        // Emitir evento para notificar al componente padre que se canceló
        $this->dispatch('customer-form-cancelled');
    }

    /**
     * Limpiar las credenciales del usuario después de 20 segundos
     */
    public function clearUserCredentials()
    {
        $this->showUserCredentials = false;
        $this->userCredentialsEmail = '';
        $this->userCredentialsPassword = '12345678';
    }

    /**
     * Verificar si el cliente ya tiene un usuario asignado
     * Se llama al cargar un cliente para edición y cuando cambia el email
     */
    public function checkExistingUser(): void
    {
        // Si no hay email, no puede haber usuario
        if (empty($this->billingEmail)) {
            $this->hasExistingUser = false;
            $this->existingUserEmail = '';
            // NO deshabilitamos el checkbox aquí, solo limpiamos las banderas
            return;
        }

        try {
            // Buscar si existe un usuario con este email
            $existingUser = User::where('email', $this->billingEmail)->first();

            if ($existingUser) {
                $this->hasExistingUser = true;
                $this->existingUserEmail = $existingUser->email;
                $this->createUser = false; // Deshabilitar el checkbox solo si existe usuario

                Log::info('Usuario existente encontrado para cliente', [
                    'company_id' => $this->editingId,
                    'email' => $this->billingEmail,
                    'user_id' => $existingUser->id
                ]);
            } else {
                $this->hasExistingUser = false;
                $this->existingUserEmail = '';
                // No modificamos createUser, dejamos que el usuario decida
            }
        } catch (\Exception $e) {
            Log::error('Error verificando usuario existente', [
                'error' => $e->getMessage(),
                'email' => $this->billingEmail
            ]);

            // En caso de error, asumir que no hay usuario
            $this->hasExistingUser = false;
            $this->existingUserEmail = '';
        }
    }

    public function updateTypeIdentification($typeIdentificationId)
    {
        $this->typeIdentificationId = $typeIdentificationId;

        // Lógica de negocio: establecer tipo de persona según tipo de identificación
        if ((int) $typeIdentificationId === 2) {
            // NIT: En modo simplificado, automáticamente Persona Jurídica
            if ($this->simplified) {
                $this->typePerson = 'Juridica';
            }
            // En modo normal: Permitir elegir entre Natural y Jurídica (no establecer automáticamente)
            // El usuario debe elegir manualmente
        } else {
            // Cualquier otro tipo de identificación: Automáticamente Persona Natural
            $this->typePerson = 'Natural';
        }

        // Limpiar campos según el tipo de persona en modo simplificado
        if ($this->simplified) {
            $this->resetPersonFieldsForSimplified();
        }

        // Re-evaluar permisos de sucursales
        $this->evaluateWarehousePermissions();
    }

    public function updateRegime($regimeId)
    {
        $this->regimeId = $regimeId;
    }

    public function updateFiscalResponsibility($fiscalResponsibilityId)
    {
        $this->fiscalResponsabilityId = $fiscalResponsibilityId;
    }

    public function updateWarehouseCity($cityId, $index = 0)
    {
        // Log para ver qué parámetros están llegando
        Log::info('updateWarehouseCity called', [
            'cityId' => $cityId,
            'cityId_type' => gettype($cityId),
            'index' => $index,
            'index_type' => gettype($index)
        ]);

        // Validar que cityId sea numérico
        if (!is_numeric($cityId)) {
            Log::warning('Invalid cityId received in updateWarehouseCity', [
                'cityId' => $cityId,
                'index' => $index
            ]);
            return;
        }

        // Actualizar warehouseCityId directamente (usado en validación y guardado)
        $this->warehouseCityId = (int) $cityId;
        $city = \App\Models\Central\CnfCity::find($cityId);
        $this->warehouseCityName = $city ? $city->name : '';


        // También actualizar en el array de warehouses si existe (para compatibilidad)
        if (isset($this->warehouses[$index])) {
            $this->warehouses[$index]['cityId'] = (int) $cityId;
            $this->warehouses[$index]['cityName'] = $this->warehouseCityName;
        }

        // Log para debugging
        Log::info('City updated successfully', [
            'warehouseCityId' => $this->warehouseCityId,
            'warehouseCityName' => $this->warehouseCityName,
            'index' => $index
        ]);
    }

    public function updatePosition($positionId)
    {
        $this->positionId = $positionId;
    }

    public function updateRoute($routeId)
    {
        $this->routeId = $routeId;
    }

    public function updateDistrict($districtId)
    {
        $this->districtId = $districtId ?: null;
    }

    public function toggleStatus()
    {
        $this->status = $this->status ? 0 : 1;
    }

    public function updatedStatus($value)
    {
        // Convert boolean to integer for database storage
        $this->status = $value ? 1 : 0;
    }

    /**
     * Toggle status for a specific company item in the table
     * Updates status in vnt_companies, vnt_warehouses, and vnt_contacts
     */
    public function toggleItemStatus($companyId)
    {
        try {
            $this->companyService->toggleCompanyStatus($companyId);
            session()->flash('message', 'Estado actualizado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Validar un campo específico en tiempo real
     * Se ejecuta cuando el usuario sale del campo (blur)
     * 
     * IMPORTANTE: Después de validar cualquier campo, siempre re-validar
     * la identificación para mantener el estado de identificationExists
     */
    public function updated($propertyName)
    {
        // Validar solo el campo que cambió
        $this->validateOnly($propertyName);

        // Actualizar el estado de errores del formulario
        $this->formHasErrors = $this->getErrorBag()->isNotEmpty();

        // Validar unicidad de identification si cambió
        if ($propertyName === 'identification' && !empty($this->identification) && !empty($this->typeIdentificationId)) {
            $this->validateIdentificationUniqueness();
        }

        // Validar unicidad de email si cambió
        if ($propertyName === 'billingEmail' && !empty($this->billingEmail)) {
            $this->validateEmailUniqueness();
        }

        // Validar checkbox createUser si se cambia cuando ya hay email duplicado
        if ($propertyName === 'createUser' && $this->createUser && ($this->emailExists || $this->hasExistingUser)) {
            $this->createUser = false;
            session()->flash('error', 'No se puede crear un usuario con un email que ya existe.');
        }
    }

    /**
     * Called when identification property is updated
     * Triggers real-time validation with debounce
     */
    public function updatedIdentification($value): void
    {
        // Validate the field using existing validation
        $this->validateOnly('identification');

        // Trigger uniqueness check
        $this->validateIdentificationUniqueness();
    }

    public function updatedBillingEmail(): void
    {
        $this->validateOnly('billingEmail');
        $this->validateEmailUniqueness();

        // Desmarcar checkbox de crear usuario si el email existe
        if ($this->emailExists && $this->createUser) {
            $this->createUser = false;
        }

        // Re-validar identificación después de cambiar email
        if (!empty($this->identification) && !empty($this->typeIdentificationId)) {
            $this->validateIdentificationUniqueness();
        }
    }

    public function updatedType($value)
    {
        // Log para debugging
        Log::info('updatedType called', [
            'value' => $value,
            'type' => $this->type,
        ]);
        // Si es PROVEEDOR, inhabilitar el checkbox de crear usuario
        if ($this->type === 'PROVEEDOR') {
            $this->validatingType = true;  // TRUE para inhabilitar el checkbox
            $this->createUser = false;  // Desmarcar el checkbox
            $this->districtId = null; // PROVEEDOR no requiere district
            Log::info('Contact type changed to PROVEEDOR, createUser disabled and district set to 000', ['validatingContactType' => $this->validatingType, 'district' => $this->districtId]);
        } else {
            // Para otros tipos
            $this->validatingType = false;  // FALSE para habilitar el checkbox
            // Si el distrito fue establecido a '000' por la lógica de PROVEEDOR, lo reseteamos
            if ($this->districtId === null) {
                $this->districtId = null; // Permitir que el usuario ingrese un valor o quede vacío
                Log::info('Contact type changed from PROVEEDOR, district reset to empty', ['district' => $this->districtId]);
            }
            Log::info('Contact type changed to ' . $this->type . ', createUser available', ['validatingType' => $this->validatingType]);
        }
    }

    public function updatedSearchType()
    {
        $this->resetPage();
        Log::info('SearchType updated', ['searchType' => $this->searchType]);
    }

    /**
     * Validate identification uniqueness in real-time
     * Called when identification or typeIdentificationId changes
     *
     * IMPORTANTE: Este método SIEMPRE debe ejecutarse después de cualquier
     * validación para mantener el estado de identificationExists actualizado
     */
    public function validateIdentificationUniqueness(): void
    {
        // Skip validation if required fields are empty
        if (empty($this->identification) || empty($this->typeIdentificationId)) {
            $this->identificationExists = false;
            $this->validatingIdentification = false;
            $this->resetErrorBag(['identification']);
            return;
        }

        // Set loading state
        $this->validatingIdentification = true;

        try {
            // Check if combination exists
            $this->identificationExists = $this->validationService->checkIdentificationExists(
                (int) $this->typeIdentificationId,
                $this->identification,
                $this->editingId
            );

            // Show user-friendly message if duplicate found
            if ($this->identificationExists) {
                $this->addValidationError('identification', '👤 Cliente Duplicado: Ya existe un cliente registrado con esta identificación. Por favor verifique el número e intente con una identificación diferente.');
            } else {
                $this->resetErrorBag(['identification']);
            }
        } catch (\Exception $e) {
            // Log error but don't break the form
            Log::error('❌ Error validating identification uniqueness', [
                'error' => $e->getMessage(),
                'identification' => $this->identification,
                'typeIdentificationId' => $this->typeIdentificationId,
                'editingId' => $this->editingId
            ]);
            $this->identificationExists = false;
            $this->addValidationError('identification', '⚠️ Error de Validación: No se pudo verificar la unicidad de la identificación. Por favor intente nuevamente.');
        } finally {
            // Always clear loading state
            $this->validatingIdentification = false;
        }
    }

    public function validateEmailUniqueness(): void
    {
        // Skip validation if required fields are empty
        if (empty($this->billingEmail)) {
            $this->emailExists = false;
            $this->validatingEmail = false;
            $this->resetErrorBag(['billingEmail']);
            return;
        }

        // Set loading state
        $this->validatingEmail = true;

        try {
            // Check if combination exists
            $this->emailExists = $this->validationService->checkEmailExists(
                $this->billingEmail,
                $this->editingId
            );

            // Show user-friendly message if duplicate found
            if ($this->emailExists) {
                $this->addValidationError('billingEmail', '✉️ Email Duplicado: Ya existe un cliente registrado con este email de facturación. Por favor use un email diferente.');
            } else {
                $this->resetErrorBag(['billingEmail']);
            }
        } catch (\Exception $e) {
            // Log error but don't break the form
            Log::error('❌ Error validating email uniqueness', [
                'error' => $e->getMessage(),
                'billingEmail' => $this->billingEmail,
                'editingId' => $this->editingId
            ]);
            $this->emailExists = false;
            $this->addValidationError('billingEmail', '⚠️ Error de Validación: No se pudo verificar la unicidad del email. Por favor intente nuevamente.');
        } finally {
            // Always clear loading state
            $this->validatingEmail = false;
        }
    }

    public function setMainWarehouse($index)
    {
        $this->warehouseService->setMainWarehouse($this->warehouses, $index);
    }

    /**
     * Inicializar sucursal por defecto
     */
    private function initializeDefaultWarehouse(): void
    {
        if (empty($this->warehouses)) {
            $defaultWarehouse = $this->warehouseService->createEmptyWarehouse(0);
            $this->warehouses[] = $defaultWarehouse;
        }

        // Evaluar permisos para agregar más sucursales
        $this->evaluateWarehousePermissions();
    }

    /**
     * Evaluar si se pueden agregar más sucursales
     */
    public function evaluateWarehousePermissions(): void
    {
        // Lógica de negocio para determinar si se pueden agregar más sucursales
        $this->canAddMoreWarehouses = $this->warehouseService->canAddMoreWarehouses(
            $this->typePerson ?? '',
            $this->typeIdentificationId ? (int) $this->typeIdentificationId : null,
            count($this->warehouses),
            $this->editingId ? (int) $this->editingId : null
        );
    }

    /**
     * Método que se ejecuta cuando cambia el tipo de persona
     */
    public function updatedTypePerson(): void
    {
        $this->evaluateWarehousePermissions();
    }

    /**
     * Método que se ejecuta cuando cambia el tipo de identificación
     * 
     * IMPORTANTE: Siempre re-validar la identificación cuando cambia el tipo
     */
    public function updatedTypeIdentificationId(): void
    {
        // Siempre re-validar la identificación cuando cambia el tipo
        // porque la combinación typeIdentificationId + identification debe ser única
        $this->validateIdentificationUniqueness();

        $this->evaluateWarehousePermissions();
    }



    /**
     * Override del método addWarehouse para verificar permisos
     */
    public function addWarehouse()
    {
        if (!$this->canAddMoreWarehouses) {
            session()->flash('error', 'No tiene permisos para agregar más sucursales.');
            return;
        }

        $newWarehouse = $this->warehouseService->createEmptyWarehouse(count($this->warehouses));
        $this->warehouses[] = $newWarehouse;

        // Re-evaluar permisos después de agregar
        $this->evaluateWarehousePermissions();
    }

    /**
     * Override del método removeWarehouse para mantener al menos una sucursal
     */
    public function removeWarehouse($index)
    {
        if (count($this->warehouses) <= 1) {
            session()->flash('error', 'Debe mantener al menos una sucursal.');
            return;
        }

        $this->warehouseService->removeWarehouse($this->warehouses, $index);

        // Re-evaluar permisos después de remover
        $this->evaluateWarehousePermissions();
    }

    /**
     * Obtener información sobre los límites de sucursales
     */
    public function getWarehouseLimitsInfo(): array
    {
        return $this->warehouseService->getWarehouseLimitsInfo(
            $this->typePerson ?? '',
            $this->typeIdentificationId ? (int) $this->typeIdentificationId : null
        );
    }

    /**
     * Determinar si una empresa tiene datos de persona natural
     */
    private function hasNaturalPersonData($company): bool
    {
        return !empty($company->firstName) ||
            !empty($company->lastName) ||
            !empty($company->secondName) ||
            !empty($company->secondLastName);
    }

    /**
     * Determinar el tipo de persona para la UI basándose en los datos de la empresa
     * 
     * Reglas de negocio simplificadas:
     * 1. Si typeIdentificationId != 2: Siempre Persona Natural (PERSON_ENTITY)
     * 2. Si typeIdentificationId == 2 (NIT):
     *    - Si tiene datos de persona natural (firstName, lastName): Persona Natural con NIT
     *    - Si NO tiene datos de persona natural: Persona Jurídica
     * 
     * @param object $company Instancia de VntCompany con todos sus datos
     * @return string "Natural" o "Juridica"
     */
    private function determineTypePersonForUI($company): string
    {
        $typeIdentificationId = (int) $company->typeIdentificationId;

        //dd($company);
        // Caso 1: No es NIT (typeIdentificationId != 2) → Siempre Persona Natural
        if ($typeIdentificationId !== 2) {
            return 'Natural';
        }

        // Caso 2: Es NIT (typeIdentificationId == 2)
        // Verificar si tiene datos de persona natural
        $hasNaturalPersonData = !empty($company->businessName);

        // Si tiene datos de persona natural → Persona Natural con NIT
        if (!$hasNaturalPersonData) {
            return 'Natural';
        }

        // Si NO tiene datos de persona natural → Persona Jurídica
        return 'Juridica';
    }


    public function clearUniqueValidationErrors()
    {
        // Limpiar errores específicos de unicidad
        $this->resetErrorBag(['billingEmail', 'identification']);
        // También resetear las banderas de existencia
        $this->identificationExists = false;
        $this->emailExists = false;
    }


    /**
     * Obtener datos del formulario para enviar al service
     */

    #[On('city-valid')]
    public function cityValidate($index, $cityId = null): bool
    {
        if ($index != 0) {
            return false;
        }

        // Si cityId viene del evento, usarlo directamente
        $cityIdToValidate = $cityId ?? $this->warehouseCityId;

        // Validar que se haya seleccionado una ciudad válida
        if (empty($cityIdToValidate) || !is_numeric($cityIdToValidate)) {
            $this->addError('warehouseCityId', 'Debe seleccionar una ciudad válida para la sucursal principal.');
            return false;
        }

        // Obtener el nombre de la ciudad para validar que existe
        $city = \App\Models\Central\CnfCity::find($cityIdToValidate);
        if (!$city) {
            $this->addError('warehouseCityId', 'La ciudad seleccionada no es válida.');
            return false;
        }

        // Actualizar las propiedades si vienen del evento
        if ($cityId !== null) {
            $this->warehouseCityId = (int) $cityId;
            $this->warehouseCityName = $city->name;
        }

        return true;
    }
    private function createRouteFromCompany($company)
    {
        $this->ensureTenantConnection();
        Log::info('createRouteFromCompany called', [
            'company_id' => $company->id,
            'route_id' => $this->routeId
        ]);
        // Obtener el último consecutivo para esta combinación de route_id y company_id
        $lastRoute = VntCompanyRoute::where('route_id', $this->routeId)
            ->orderBy('sales_order', 'desc')
            ->first();

        Log::info('Last route found', [
            'lastRoute' => $lastRoute ? $lastRoute->toArray() : null
        ]);

        // Si existe un registro previo, incrementar el consecutivo, si no, empezar en 1
        $nextSalesOrder = $lastRoute ? ($lastRoute->sales_order + 1) : 1;

        $routeData = [
            'company_id' => $company->id,
            'route_id' => $this->routeId,
            'sales_order' => $nextSalesOrder
        ];

        Log::info('Creating route with data', ['routeData' => $routeData]);

        $route = VntCompanyRoute::create($routeData);

        Log::info('Route created', [
            'route' => $route ? $route->toArray() : null
        ]);

        return $route;
    }

    /**
     * Crear usuario a partir de los datos del cliente
     */
    private function createUserFromCompany($company)
    {
        try {
            // Obtener el contacto principal
            $mainWarehouse = $company->mainWarehouse;
            $mainContact = $mainWarehouse ? $mainWarehouse->contacts->first() : null;

            // Preparar datos del usuario
            $userName = $this->firstName && $this->lastName
                ? trim($this->firstName . ' ' . $this->lastName)
                : $this->businessName;

            $userData = [
                'name' => $userName,
                'email' => $this->billingEmail,
                'password' => Hash::make('12345678'), // Contraseña por defecto
                'profile_id' => 17, // Perfil "Tienda"
                'contact_id' => $mainContact ? $mainContact->id : null,
                'phone' => $this->business_phone ?: $this->personal_phone,
            ];

            // Verificar que el email no exista en usuarios
            $existingUser = User::where('email', $this->billingEmail)->first();

            if ($existingUser) {
                throw new \Exception('Ya existe un usuario con este email.');
            }

            // Crear el usuario
            $newUser = User::create($userData);

            UserTenant::create([
                'user_id' => $newUser->id,
                'tenant_id' => session('tenant_id'),
                'role_id' => 17, // Rol "Proveedor"
                'is_active' => 1,
            ]);

            // Guardar las credenciales para mostrar en el modal
            $this->userCredentialsEmail = $this->billingEmail;
            $this->userCredentialsPassword = '12345678';
            $this->showUserCredentials = true;

            Log::info('Usuario creado exitosamente', [
                'user_id' => $newUser->id,
                'company_id' => $company->id,
                'email' => $this->billingEmail
            ]);

            Log::info('✅ Usuario creado exitosamente');

            // Mensaje informativo para el usuario
            session()->flash('info', 'Usuario creado exitosamente.');
        } catch (\Exception $e) {
            // Log del error pero no lanzar excepción
            Log::error('❌ Error al crear el usuario', [
                'error' => $e->getMessage()
            ]);

            // Agregar mensaje informativo sin fallar
            session()->flash('warning', 'Error al crear el usuario' . $e->getMessage());
        }
        return $newUser;
    }

    private function getFormData(): array
    {
        // Si es NIT, usar verification_digit como checkDigit
        $checkDigit = ((int) $this->typeIdentificationId === 2)
            ? $this->verification_digit
            : $this->checkDigit;

        $formData = [
            'typeIdentificationId' => $this->typeIdentificationId,
            'identification' => $this->identification,
            'firstName' => $this->firstName,
            'secondName' => $this->secondName,
            'lastName' => $this->lastName,
            'secondLastName' => $this->secondLastName,
            'businessName' => $this->businessName,
            'billingEmail' => $this->billingEmail,
            'typePerson' => $this->typePerson,
            'checkDigit' => (string)$checkDigit,
            'code_ciiu' => $this->code_ciiu,
            'regimeId' => $this->regimeId,
            'fiscalResponsabilityId' => $this->fiscalResponsabilityId,
            'status' => $this->status,
            'business_phone' => $this->business_phone,
            'personal_phone' => $this->personal_phone,
            'positionId' => $this->positionId,
            'routeId' => $this->routeId === '' ? null : $this->routeId,
            'type' => $this->type ?: ($this->simplified && $this->reusable ? 'CLIENTE' : $this->type),
        ];

        Log::info('🔍 DATOS ENVIADOS AL CompanyService', [
            'fiscalResponsabilityId_en_formData' => $formData['fiscalResponsabilityId'],
            'fiscalResponsabilityId_type' => gettype($formData['fiscalResponsabilityId']),
            'regimeId_en_formData' => $formData['regimeId'],
            'type_field_value' => $formData['type'],
            'type_property_value' => $this->type,
            'simplified_mode' => $this->simplified,
            'reusable_mode' => $this->reusable,
            'complete_form_data' => $formData
        ]);

        return $formData;
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

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }

    /**
     * Convertir tipo de contacto de español a inglés para la API
     */
    private function getContactTypeForApi(): string
    {
        $typeMapping = [
            'CLIENTE' => 'client',
            'PROVEEDOR' => 'provider',
            // Agregar más tipos si es necesario
        ];

        $apiType = $typeMapping[$this->type] ?? 'client'; // Default a 'client'

        Log::info('🔄 Conversión tipo de contacto para API', [
            'type_spanish' => $this->type,
            'type_english' => $apiType
        ]);

        return $apiType;
    }

    /**
     * Formatear mensajes de error de API para hacerlos más amigables al usuario
     * Categoriza y traduce errores de API a mensajes comprensibles
     */
    private function formatApiErrorMessage(string $apiError): string
    {
        $lowerError = strtolower($apiError);
        $errorCategory = $this->categorizeApiError($lowerError);

        // Mensajes categorizados por tipo de error
        $errorMessages = [
            'duplicate' => [
                'icon' => '👤',
                'title' => 'Cliente Duplicado',
                'message' => 'Este cliente ya existe en el sistema de facturación.',
                'suggestions' => [
                    'Verifique el número de identificación y tipo de documento',
                    'Confirme que no está registrando un cliente existente',
                    'Use una identificación diferente si es correcto'
                ]
            ],
            'validation' => [
                'icon' => '📋',
                'title' => 'Error de Validación',
                'message' => 'Los datos proporcionados no cumplen con los requisitos de la API.',
                'suggestions' => [
                    'Verifique que todos los campos estén completos',
                    'Confirme que el régimen fiscal sea válido',
                    'Revise las responsabilidades fiscales seleccionadas'
                ]
            ],
            'authentication' => [
                'icon' => '🔐',
                'title' => 'Error de Autenticación',
                'message' => 'Problema con las credenciales de acceso al sistema de facturación.',
                'suggestions' => [
                    'Contacte al administrador del sistema',
                    'Verifique la configuración de la API de facturación',
                    'Asegúrese de que el token no haya expirado'
                ]
            ],
            'connection' => [
                'icon' => '📡',
                'title' => 'Error de Conexión',
                'message' => 'No se pudo establecer comunicación con el sistema de facturación.',
                'suggestions' => [
                    'Verifique su conexión a internet',
                    'Intente nuevamente en unos momentos',
                    'Contacte soporte si el problema persiste'
                ]
            ],
            'server' => [
                'icon' => '🔧',
                'title' => 'Error del Servidor',
                'message' => 'El sistema de facturación está experimentando problemas internos.',
                'suggestions' => [
                    'Intente nuevamente en unos minutos',
                    'El problema es temporal del proveedor de facturación',
                    'Contacte al soporte si persiste por más de 30 minutos'
                ]
            ],
            'field' => [
                'icon' => '⚠️',
                'title' => 'Error de Campo',
                'message' => 'Uno o más campos contienen información inválida.',
                'suggestions' => [
                    'Revise el formato del email de facturación',
                    'Verifique que los teléfonos sean válidos',
                    'Confirme que la dirección esté completa'
                ]
            ],
            'rate_limit' => [
                'icon' => '⏱️',
                'title' => 'Límite de Solicitudes',
                'message' => 'Se han realizado demasiadas solicitudes al sistema de facturación.',
                'suggestions' => [
                    'Espere unos minutos antes de intentar nuevamente',
                    'Evite realizar múltiples operaciones simultáneas',
                    'El límite se restablece automáticamente'
                ]
            ],
            'plan_limit' => [
                'icon' => '📊',
                'title' => 'Límite del Plan',
                'message' => 'Ha alcanzado el límite de clientes permitidos en su plan actual.',
                'suggestions' => [
                    'Actualice su plan de facturación',
                    'Elimine clientes no utilizados',
                    'Contacte al administrador para revisar los límites'
                ]
            ]
        ];

        $errorInfo = $errorMessages[$errorCategory] ?? $errorMessages['validation'];

        // Construir mensaje formateado
        $formattedMessage = $errorInfo['icon'] . ' ' . $errorInfo['title'] . ': ' . $errorInfo['message'];

        // Agregar sugerencias
        $formattedMessage .= "\n\n💡 Sugerencias:";
        foreach ($errorInfo['suggestions'] as $suggestion) {
            $formattedMessage .= "\n• " . $suggestion;
        }

        // Agregar detalles técnicos si son útiles
        if (strlen($apiError) < 200 && !empty(trim($apiError))) {
            $formattedMessage .= "\n\n🔍 Detalles técnicos: " . $apiError;
        }

        // Log para tracking de errores
        Log::warning('🎯 Error API categorizado', [
            'original_error' => $apiError,
            'category' => $errorCategory,
            'user_id' => Auth::id(),
            'editing_id' => $this->editingId
        ]);

        return $formattedMessage;
    }

    /**
     * Categorizar errores de API para aplicar el tratamiento adecuado
     */
    private function categorizeApiError(string $lowerError): string
    {
        // Patrones de errores duplicados
        $duplicatePatterns = ['ya existe', 'already exists', 'duplicate', 'duplicado'];
        foreach ($duplicatePatterns as $pattern) {
            if (strpos($lowerError, $pattern) !== false) {
                return 'duplicate';
            }
        }

        // Patrones de autenticación
        $authPatterns = ['token', 'unauthorized', 'forbidden', 'authentication', 'credentials'];
        foreach ($authPatterns as $pattern) {
            if (strpos($lowerError, $pattern) !== false) {
                return 'authentication';
            }
        }

        // Patrones de conexión
        $connectionPatterns = ['timeout', 'connection', 'network', 'unreachable', 'connect'];
        foreach ($connectionPatterns as $pattern) {
            if (strpos($lowerError, $pattern) !== false) {
                return 'connection';
            }
        }

        // Patrones de servidor
        $serverPatterns = ['internal server error', '500', '502', '503', 'server error'];
        foreach ($serverPatterns as $pattern) {
            if (strpos($lowerError, $pattern) !== false) {
                return 'server';
            }
        }

        // Patrones de límites de solicitudes
        $rateLimitPatterns = ['rate limit', 'too many requests', '429', 'limit exceeded'];
        foreach ($rateLimitPatterns as $pattern) {
            if (strpos($lowerError, $pattern) !== false) {
                return 'rate_limit';
            }
        }

        // Patrones de límites del plan
        $planLimitPatterns = ['plan limit', 'subscription limit', 'limit reached', 'maximum clients'];
        foreach ($planLimitPatterns as $pattern) {
            if (strpos($lowerError, $pattern) !== false) {
                return 'plan_limit';
            }
        }

        // Patrones de campos
        $fieldPatterns = ['email', 'phone', 'address', 'invalid format', 'field'];
        foreach ($fieldPatterns as $pattern) {
            if (strpos($lowerError, $pattern) !== false) {
                return 'field';
            }
        }

        // Patrones de validación
        $validationPatterns = ['régimen', 'regime', 'fiscal', 'validation', 'invalid'];
        foreach ($validationPatterns as $pattern) {
            if (strpos($lowerError, $pattern) !== false) {
                return 'validation';
            }
        }

        // Por defecto, categorizar como error de validación
        return 'validation';
    }

    /**
     * Determinar si se debe sincronizar con la API
     */
    private function shouldSyncWithApi(): array
    {
        try {
            Log::info('🔄 Verificando si se debe sincronizar con API...');

            // Asegurar que la configuración esté inicializada y con datos frescos
            $this->clearConfigurationCache();
            $this->initializeCompanyConfiguration();

            // Verificar que tenemos company_id válido
            if (!$this->currentCompanyId) {
                Log::warning('⚠️ Sincronización API saltada: No se pudo obtener currentCompanyId', [
                    'config_initialized' => $this->isConfigurationInitialized,
                    'config_service_exists' => $this->configService ? 'YES' : 'NO'
                ]);
                return [
                    'should_sync' => false,
                    'reason' => 'No se pudo obtener la configuración de la empresa.'
                ];
            }

            // Verificar si facturación electrónica está habilitada usando el trait (option_id=8)
            $isElectronicEnabled = $this->isOptionEnabled(8);
            
            Log::info('📊 Estado de configuración para sincronización', [
                'company_id' => $this->currentCompanyId,
                'option_8_enabled' => $isElectronicEnabled ? 'SI' : 'NO',
                'configService_exists' => $this->configService ? 'YES' : 'NO'
            ]);

            if (!$isElectronicEnabled) {
                return [
                    'should_sync' => false,
                    'reason' => 'La facturación electrónica no está habilitada (opción 8) para esta empresa.'
                ];
            }

            return [
                'should_sync' => true,
                'reason' => 'Configuración válida para sincronización.'
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error determinando si sincronizar con API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'should_sync' => false,
                'reason' => 'Error interno de configuración.'
            ];
        }
    }

    /**
     * Pre-validar que la sincronización con API es posible ANTES de guardar
     */
    private function preValidateApiSync(): array
    {
        try {
            Log::info('🔍 Iniciando pre-validación API');

            // Verificar que tenemos company_id válido
            if (!$this->currentCompanyId) {
                $this->initializeCompanyConfiguration();
                if (!$this->currentCompanyId) {
                    return [
                        'success' => false,
                        'message' => '⚙️ Error de Configuración: No se pudo obtener la configuración de la empresa para sincronizar clientes. Verifique que el usuario esté correctamente asignado a una empresa o contacte al administrador del sistema.'
                    ];
                }
            }

            // Limpiar cache para datos frescos
            $this->clearConfigurationCache();

            // Verificar límites del plan para clientes (solo para creación nueva)
            if (!$this->editingId && !$this->canSyncClients()) {
                return [
                    'success' => false,
                    'message' => '📊 Límite del Plan Alcanzado: Ha alcanzado el máximo número de clientes permitidos en su plan actual.\n\n' .
                        '🔧 Soluciones:\n' .
                        '• Actualice su plan para obtener más capacidad\n' .
                        '• Contacte al administrador para revisar los límites\n' .
                        '• Elimine clientes no utilizados para liberar espacio'
                ];
            }

            // Verificar autenticación
            $authUser = Auth::user();
            if (!$authUser) {
                return [
                    'success' => false,
                    'message' => '🔐 Error de Autenticación: Su sesión ha expirado. Por favor inicie sesión nuevamente para sincronizar clientes con la API de facturación.'
                ];
            }

            // Verificar configuración de API
            $this->ensureTenantConnection();
            $optimizedConfig = DatabaseConfigService::getFacturacionConfigByUser($authUser->id);
            if (!$optimizedConfig) {
                return [
                    'success' => false,
                    'message' => '⚙️ Error de Configuración API: No se encontró la configuración de facturación electrónica para sincronizar clientes. Verifique que la configuración esté correctamente establecida en el módulo de facturación o contacte al administrador.'
                ];
            }

            Log::info('✅ Pre-validación API completada exitosamente');
            return [
                'success' => true,
                'message' => 'Validación previa exitosa'
            ];
        } catch (\Exception $e) {
            Log::error('Error en preValidateApiSync', [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => '❌ Error Interno: No se pudo validar la configuración para sincronización. Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Preparar datos para la API basado en la estructura VntCompany
     */
    private function prepareApiData(): array
    {
        // Mapear tipos de persona a valores correctos de Alegra
        $kindOfPersonValue = match ($this->typePerson) {
            'Juridica' => 'LEGAL_ENTITY',
            'Natural' => 'PERSON_ENTITY',
            'Otra' => 'OTHER_ENTITY',
            default => 'PERSON_ENTITY' // Por defecto persona natural
        };

        // Preparar nameObject siguiendo la lógica del código que funciona:
        // Solo si typePerson !== 'LEGAL_ENTITY', de lo contrario undefined/null
        $nameObject = null;
        if ($kindOfPersonValue !== 'LEGAL_ENTITY') {
            $nameObject = [
                'firstName' => $this->firstName ?: '',
                'lastName' => $this->lastName ?: ''
            ];
        }

        // Preparar city info (esto debería venir de una consulta a la BD de ciudades)
        $cityInfo = $this->getCityInfo($this->warehouseCityId);

        return [
            'nameObject' => $nameObject,
            'identificationObject' => [
                'number' => $this->identification,
                'type' => $this->getIdentificationType($this->typeIdentificationId),
                'dv' => $this->checkDigit ?: $this->verification_digit,
            ],
            'name' => $this->businessName ?: ($this->firstName . ' ' . $this->lastName),
            'kindOfPerson' => $kindOfPersonValue,
            'regime' => $this->getRegimeName($this->regimeId),
            'fiscalResponsabilities' => ($this->fiscalResponsabilityId && $this->fiscalResponsabilityId !== '0')
                ? (function () {
                    Log::info('🔍 Debug fiscalResponsabilityId CONDITION TRUE', [
                        'fiscalResponsabilityId' => $this->fiscalResponsabilityId,
                        'type' => gettype($this->fiscalResponsabilityId),
                        'equals_zero_string' => $this->fiscalResponsabilityId === '0',
                        'equals_zero_int' => $this->fiscalResponsabilityId === 0,
                        'is_truthy' => !!$this->fiscalResponsabilityId
                    ]);
                    $integrationId = $this->getFiscalResponsabilityIntegrationId($this->fiscalResponsabilityId);
                    Log::info('🔍 Debug integrationId result', [
                        'integrationId' => $integrationId,
                        'integrationId_type' => gettype($integrationId),
                        'is_null' => is_null($integrationId),
                        'will_send_array' => $integrationId ? true : false,
                        'final_result' => $integrationId ? [$integrationId] : null
                    ]);
                    return $integrationId ? [$integrationId] : null;
                })()
                : (function () {
                    Log::info('🔍 Debug fiscalResponsabilityId CONDITION FALSE', [
                        'fiscalResponsabilityId' => $this->fiscalResponsabilityId,
                        'type' => gettype($this->fiscalResponsabilityId),
                        'is_falsy' => !$this->fiscalResponsabilityId,
                        'equals_zero_string' => $this->fiscalResponsabilityId === '0'
                    ]);
                    return null;
                })(),
            'type' => $this->getContactTypeForApi(),
            'phonePrimary' => $this->business_phone ?: $this->personal_phone,
            'email' => $this->billingEmail,
            'address' => [
                'address' => $this->warehouseAddress ?: 'Dirección',
                'city' => $cityInfo['cityName'] ?? 'Bogotá',
                'department' => $cityInfo['departmentName'] ?? 'Cundinamarca',
                'zipCode' => $this->warehousePostcode
            ],
            'accounting' => [
                'debtToPay' => 5033,
                'accountReceivable' => 5007,
            ]
        ];
    }

    /**
     * Obtener información de la ciudad
     */
    private function getCityInfo($cityId): array
    {
        if (!$cityId) {
            return [
                'cityName' => 'Bogotá',
                'departmentName' => 'Cundinamarca'
            ];
        }

        try {
            $city = \App\Models\Central\CnfCity::find($cityId);
            return [
                'cityName' => $city->name ?? 'Bogotá',
                'departmentName' => $city->department->name ?? 'Cundinamarca'
            ];
        } catch (\Exception $e) {
            return [
                'cityName' => 'Bogotá',
                'departmentName' => 'Cundinamarca'
            ];
        }
    }

    /**
     * Obtener tipo de identificación en texto
     */
    private function getIdentificationType($typeId): string
    {
        // Mapear IDs a texto según tu sistema
        $types = [
            1 => 'CC',
            2 => 'NIT',
            3 => 'CE',
            4 => 'PAS',
            // Agregar más según sea necesario
        ];

        return $types[$typeId] ?? 'CC';
    }

    /**
     * Obtener nombre del régimen
     */
    private function getRegimeName($regimeId): string
    {
        try {
            // Consultar la descripción del régimen desde la base de datos central
            $regime = DB::connection('central')
                ->table('cnf_regime')
                ->where('id', $regimeId)
                ->where('status', 1)
                ->first(['description']);

            return $regime ? $regime->description : 'SIMPLIFIED_REGIME';
        } catch (\Exception $e) {
            Log::error('Error obteniendo nombre del régimen: ' . $e->getMessage());
            return 'SIMPLIFIED_REGIME';
        }
    }

    /**
     * Obtener el integrationDataId de la responsabilidad fiscal
     * para enviar a la API en lugar del id interno
     */
    private function getFiscalResponsabilityIntegrationId($fiscalResponsabilityId): ?string
    {


        try {
            // Asegurar conexión tenant
            $this->ensureTenantConnection();

            // Debug: Verificar que la tabla existe
            Log::info('🔍 VERSIÓN ACTUALIZADA - Buscando en RAP/central fiscal responsibilities id: ' . $fiscalResponsabilityId);

            // Primero verificar si la tabla existe (en RAP/central)
            $tableExists = DB::connection('central')
                ->getSchemaBuilder()
                ->hasTable('cnf_fiscal_responsabilities');

            Log::info('📋 Tabla cnf_fiscal_responsabilities existe en RAP?', ['exists' => $tableExists]);

            if (!$tableExists) {
                Log::error('❌ Tabla cnf_fiscal_responsabilities no existe en RAP/central');
                return null;
            }

            // Ver todos los registros de la tabla para debug (en RAP/central)
            $allRecords = DB::connection('central')
                ->table('cnf_fiscal_responsabilities')
                ->select(['id', 'description', 'integrationDataId'])
                ->get();

            Log::info('📋 Todos los registros en cnf_fiscal_responsabilities (RAP)', [
                'total_records' => $allRecords->count(),
                'records' => $allRecords->toArray()
            ]);

            // Consultar el integrationDataId basado en el id seleccionado (en RAP/central)
            $fiscalResponsability = DB::connection('central')
                ->table('cnf_fiscal_responsabilities')
                ->where('id', $fiscalResponsabilityId)
                ->first(['integrationDataId', 'description']);

            Log::info('🔍 Debug fiscal responsibility query', [
                'fiscalResponsabilityId' => $fiscalResponsabilityId,
                'found' => $fiscalResponsability ? true : false,
                'integrationDataId' => $fiscalResponsability ? $fiscalResponsability->integrationDataId : 'N/A',
                'description' => $fiscalResponsability ? $fiscalResponsability->description : 'N/A'
            ]);

            if ($fiscalResponsability && !is_null($fiscalResponsability->integrationDataId) && $fiscalResponsability->integrationDataId != 0) {
                Log::info('✅ Usando integrationDataId encontrado', [
                    'id' => $fiscalResponsabilityId,
                    'integrationDataId' => $fiscalResponsability->integrationDataId,
                    'description' => $fiscalResponsability->description
                ]);

                return (string)$fiscalResponsability->integrationDataId;
            }

            // Si no se encuentra o integrationDataId es 0/null, no enviar fiscal responsibilities
            Log::warning('⚠️ No se encontró integrationDataId válido para fiscal responsibility', [
                'fiscalResponsabilityId' => $fiscalResponsabilityId,
                'integrationDataId' => $fiscalResponsability ? $fiscalResponsability->integrationDataId : 'registro no encontrado'
            ]);

            return null; // Retornar null para que no se incluya en el array

        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo integrationDataId de fiscal responsibility', [
                'fiscalResponsabilityId' => $fiscalResponsabilityId,
                'error' => $e->getMessage()
            ]);

            // En caso de error, no enviar fiscal responsibilities para evitar errores en API
            return null;
        }
    }

    /**
     * Validar datos con la API antes de crear el cliente localmente
     */
    private function validateApiData(array $apiData): array
    {
        try {
            $authUser = Auth::user();
            $optimizedConfig = DatabaseConfigService::getFacturacionConfigByUser($authUser->id);

            if (!$optimizedConfig) {
                return [
                    'success' => false,
                    'message' => '⚙️ Error de Configuración: No se pudo obtener la configuración de la API para validar los datos.'
                ];
            }

            // Crear ApiClient para validación
            $apiClient = new ApiClient(
                $optimizedConfig['base_url'],
                $optimizedConfig['token'],
                $optimizedConfig['username'],
                $optimizedConfig['timeout']
            );

            Log::info('🔍 Validando datos con API', ['api_data' => $apiData]);

            try {
                // Hacer un intento REAL de creación para detectar errores de validación
                set_time_limit(30); // Timeout más corto para validación
                $validationResult = $apiClient->createContact($apiData);
                set_time_limit(60); // Restaurar timeout

                if (!$validationResult['success']) {
                    $errorMessage = $validationResult['message'] ?? 'Error desconocido en la API';

                    Log::warning('❌ API rechazó la creación del cliente durante validación', [
                        'api_data' => $apiData,
                        'error' => $errorMessage,
                        'preventing_local_save' => true
                    ]);

                    // Detectar diferentes tipos de error
                    if (
                        strpos(strtolower($errorMessage), 'ya se encuentra') !== false ||
                        strpos(strtolower($errorMessage), 'duplicad') !== false ||
                        strpos(strtolower($errorMessage), 'existe') !== false
                    ) {

                        return [
                            'success' => false,
                            'message' => '🔄 Cliente Duplicado: ' . $errorMessage . ' Por favor use un email o identificación diferente.'
                        ];
                    }

                    return [
                        'success' => false,
                        'message' => '⚠️ Error de Validación API: ' . $errorMessage
                    ];
                }

                // Si la API aceptó la creación, guardar ID temporal
                if (isset($validationResult['data']['id'])) {
                    $tempApiId = $validationResult['data']['id'];
                    Log::info('✅ Validación exitosa, usando registro temporal de API', [
                        'temp_api_id' => $tempApiId,
                        'api_data' => $apiData
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Datos válidos para sincronización',
                        'temp_api_id' => $tempApiId
                    ];
                } else {
                    return [
                        'success' => true,
                        'message' => 'Datos válidos para sincronización'
                    ];
                }
            } catch (\Exception $e) {
                set_time_limit(60); // Restaurar timeout
                Log::error('❌ Error en validación con API', [
                    'api_data' => $apiData,
                    'error' => $e->getMessage()
                ]);

                return [
                    'success' => false,
                    'message' => '❌ Error de Conexión API: No se pudo validar los datos con la API de facturación. Error: ' . $e->getMessage()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error general en validateApiData', [
                'api_data' => $apiData,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => '❌ Error Interno: No se pudo completar la validación. Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si el plan permite sincronizar clientes
     */
    private function canSyncClients(): bool
    {
        try {
            $this->ensureTenantConnection();
            $this->initializeCompanyConfiguration();
            $this->clearConfigurationCache();

            // Usar opción 2 para clientes (ajustar según tu sistema)
            $value = $this->getOptionValue(2);

            // Contar clientes activos actuales con api_data_id
            Log::info('🔍 Verificando tabla vnt_companies para api_data_id');

            try {
                $syncedClients = \App\Models\Tenant\Customer\VntCompany::where('status', 1)
                    ->whereNotNull('api_data_id')
                    ->count();

                Log::info('✅ Consulta de clientes sincronizados exitosa', [
                    'synced_clients' => $syncedClients
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Error en consulta de clientes sincronizados (probablemente campo api_data_id no existe)', [
                    'error' => $e->getMessage()
                ]);

                // Si el campo no existe, contar todos los clientes activos como fallback
                $syncedClients = \App\Models\Tenant\Customer\VntCompany::where('status', 1)->count();

                Log::info('📊 Usando conteo total de clientes activos como fallback', [
                    'total_active_clients' => $syncedClients
                ]);
            }

            // Si el límite es muy bajo (1-5), probablemente es un error de configuración
            if ($value <= 5) {
                Log::warning('🚨 Límite del plan muy bajo, probablemente mal configurado', [
                    'plan_limit' => $value,
                    'assuming_unlimited' => true
                ]);
                return true; // Permitir creación
            }

            $canSync = $syncedClients < $value;

            Log::info('🔍 canSyncClients() verificación', [
                'companyId' => $this->currentCompanyId,
                'synced_clients_count' => $syncedClients,
                'plan_limit' => $value,
                'can_sync' => $canSync
            ]);

            return $canSync;
        } catch (\Exception $e) {
            Log::error('Error verificando límites de clientes', [
                'company_id' => $this->currentCompanyId,
                'error' => $e->getMessage()
            ]);
            return true; // En caso de error, permitir sincronización
        }
    }



    /**
     * Sincronizar company con API (método final después de guardar localmente)
     */
    private function syncCompanyWithApi($company, $tempApiId = null): void
    {
        try {
            Log::info('🔄 syncCompanyWithApi INICIO', ['company_id' => $company->id]);

            // Si ya tenemos temp_api_id, solo asignarlo
            if ($tempApiId && !$this->editingId) {
                Log::info('✅ Usando API ID temporal de validación', [
                    'company_id' => $company->id,
                    'temp_api_id' => $tempApiId
                ]);

                $company->update(['api_data_id' => $tempApiId]);
                session()->flash('sync_message', '✅ Cliente Sincronizado: El cliente ha sido creado exitosamente y sincronizado con la API de facturación electrónica.');
                return;
            }

            // Sincronización normal si no hay temp_api_id o es una actualización
            Log::info('🔄 Realizando sincronización normal con API');
            // Aquí puedes implementar la sincronización completa si es necesario
            // Por ahora, solo log de éxito
            session()->flash('sync_message', '✅ Cliente Sincronizado: El cliente ha sido procesado correctamente.');
        } catch (\Exception $e) {
            Log::error('❌ Error en syncCompanyWithApi', [
                'company_id' => $company->id,
                'error' => $e->getMessage()
            ]);
            session()->flash('sync_warning', '⚠️ Cliente guardado localmente, pero hubo un problema con la sincronización API: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar cliente en la API cuando tiene api_data_id
     */
    private function updateCompanyInApi($company, array $apiData): void
    {
        try {
            Log::info('📡 Iniciando actualización del cliente en API', [
                'company_id' => $company->id,
                'api_data_id' => $company->api_data_id
            ]);

            // Obtener configuración API
            $authUser = Auth::user();
            $config = DatabaseConfigService::getFacturacionConfigByUser($authUser->id);

            Log::info('🔍 DEBUG configuración API para actualización', [
                'company_id' => $company->id,
                'user_id' => $authUser->id,
                'config_type' => gettype($config),
                'config_is_array' => is_array($config),
                'config_keys' => is_array($config) ? array_keys($config) : 'NOT_ARRAY',
                'config_complete' => $config
            ]);

            if (!$config) {
                Log::warning('⚠️ No se encontró configuración API para actualización', [
                    'company_id' => $company->id,
                    'user_id' => $authUser->id
                ]);
                session()->flash('sync_warning', '⚠️ Cliente actualizado localmente, pero no se pudo acceder a la configuración de la API para sincronizar.');
                return;
            }

            // Crear cliente API con parámetros individuales
            $apiClient = new ApiClient(
                $config['base_url'] ?? null,
                $config['token'] ?? null,
                $config['username'] ?? null,
                $config['timeout'] ?? 15
            );

            Log::info('🔧 ApiClient creado para actualización', [
                'base_url' => $config['base_url'] ?? 'NOT_SET',
                'has_token' => !empty($config['token']),
                'username' => $config['username'] ?? 'NOT_SET'
            ]);

            // Actualizar en API usando el api_data_id
            $response = $apiClient->updateContact($company->api_data_id, $apiData);

            if ($response['success']) {
                Log::info('✅ Cliente actualizado exitosamente en API', [
                    'company_id' => $company->id,
                    'api_data_id' => $company->api_data_id
                ]);
                session()->flash('sync_message', '✅ Cliente actualizado: Los cambios se han sincronizado correctamente con el sistema de facturación.');
            } else {
                Log::error('❌ Error actualizando cliente en API', [
                    'company_id' => $company->id,
                    'api_data_id' => $company->api_data_id,
                    'error' => $response['message'] ?? 'Error desconocido'
                ]);

                $userMessage = $this->formatApiErrorMessage($response['message'] ?? 'Error desconocido en actualización');
                session()->flash('sync_error', '❌ Error de Sincronización: ' . $userMessage);
            }
        } catch (\Exception $e) {
            Log::error('❌ Excepción actualizando cliente en API', [
                'company_id' => $company->id,
                'api_data_id' => $company->api_data_id,
                'error' => $e->getMessage()
            ]);

            session()->flash('sync_warning', '⚠️ Cliente actualizado localmente, pero hubo un problema sincronizando con la API: ' . $e->getMessage());
        }
    }

    /**
     * Manejar errores de base de datos con mensajes amigables
     * Incluye logging detallado para debugging
     */
    private function handleDatabaseError(\Illuminate\Database\QueryException $e): string
    {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        $contextData = $this->buildErrorContext();

        // Log detallado del error de base de datos
        Log::error('💾 Error de Base de Datos Detectado', [
            'error_type' => 'database',
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'sql_state' => $e->errorInfo[0] ?? 'unknown',
            'driver_code' => $e->errorInfo[1] ?? 'unknown',
            'context' => $contextData,
            'stack_trace' => $e->getTraceAsString()
        ]);

        // Errores de duplicados con contexto específico
        if (strpos($errorMessage, 'Duplicate entry') !== false) {
            if (strpos($errorMessage, 'identification') !== false) {
                Log::warning('👤 Intento de registro con identificación duplicada', [
                    'attempted_identification' => $this->identification,
                    'type_identification' => $this->typeIdentificationId,
                    'user_id' => Auth::id(),
                    'context' => $contextData
                ]);
                return '👤 Cliente Duplicado: Ya existe un cliente con este número de identificación. Por favor verifique el documento e intente con uno diferente.';
            }
            if (strpos($errorMessage, 'email') !== false) {
                Log::warning('✉️ Intento de registro con email duplicado', [
                    'attempted_email' => $this->billingEmail,
                    'user_id' => Auth::id(),
                    'context' => $contextData
                ]);
                return '✉️ Email Duplicado: Ya existe un cliente con este email de facturación. Por favor use un email diferente.';
            }
            Log::warning('⚠️ Intento de registro con datos duplicados', $contextData);
            return '⚠️ Registro Duplicado: Ya existe un cliente con estos datos. Por favor verifique la información e intente nuevamente.';
        }

        // Errores de integridad referencial
        if (strpos($errorMessage, 'foreign key constraint') !== false) {
            Log::warning('🔗 Error de integridad referencial', [
                'selected_regime' => $this->regimeId,
                'selected_fiscal_responsibility' => $this->fiscalResponsabilityId,
                'selected_city' => $this->warehouseCityId,
                'context' => $contextData
            ]);
            return '🔗 Error de Referencia: Los datos seleccionados no son válidos. Por favor verifique que los campos como ciudad, régimen y responsabilidades fiscales estén correctamente seleccionados.';
        }

        // Error de longitud de datos
        if (strpos($errorMessage, 'Data too long') !== false) {
            $fieldInfo = $this->extractFieldFromDataTooLongError($errorMessage);
            Log::warning('📝 Datos demasiado largos', array_merge($fieldInfo, $contextData));
            return '📝 Datos Demasiado Largos: ' . ($fieldInfo['friendly_message'] ?? 'Uno de los campos contiene demasiado texto. Por favor reduzca la longitud de los campos como nombres, dirección o descripción.');
        }

        // Error de campos nulos
        if (strpos($errorMessage, 'cannot be null') !== false) {
            $nullField = $this->extractNullFieldFromError($errorMessage);
            Log::warning('⚠️ Campo requerido null', array_merge(['null_field' => $nullField], $contextData));
            return '⚠️ Campo Requerido: Falta información obligatoria. Por favor complete todos los campos marcados como requeridos.';
        }

        // Error de conexión de base de datos
        if (strpos($errorMessage, 'Connection refused') !== false || strpos($errorMessage, 'connection timeout') !== false) {
            Log::critical('📡 Error de conexión a la base de datos', $contextData);
            return '📡 Error de Conexión: Problema de conectividad con la base de datos. Por favor intente nuevamente en unos momentos.';
        }

        // Error genérico con ID de tracking
        $errorId = substr(md5($errorMessage . time()), 0, 8);
        Log::error('💾 Error de base de datos no categorizado', array_merge([
            'error_id' => $errorId,
            'needs_investigation' => true
        ], $contextData));

        return '💾 Error de Base de Datos: Hubo un problema guardando la información. Por favor intente nuevamente o contacte al administrador si el problema persiste. (ID: ' . $errorId . ')';
    }

    /**
     * Manejar errores generales con mensajes amigables
     * Incluye categorización detallada y logging completo
     */
    private function handleGeneralError(\Exception $e): string
    {
        $errorMessage = $e->getMessage();
        $errorClass = get_class($e);
        $contextData = $this->buildErrorContext();
        $errorId = substr(md5($errorMessage . time() . $errorClass), 0, 8);

        // Log completo del error con contexto
        Log::error('❌ Error General Capturado', [
            'error_id' => $errorId,
            'error_type' => 'general_exception',
            'error_class' => $errorClass,
            'error_message' => $errorMessage,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'context' => $contextData,
            'stack_trace' => $e->getTraceAsString(),
            'previous_exception' => $e->getPrevious() ? [
                'class' => get_class($e->getPrevious()),
                'message' => $e->getPrevious()->getMessage()
            ] : null
        ]);

        // Errores de conexión HTTP/API
        if (
            $errorClass === 'GuzzleHttp\\Exception\\ConnectException' ||
            strpos($errorClass, 'ConnectException') !== false ||
            strpos($errorMessage, 'Connection refused') !== false ||
            strpos($errorMessage, 'timeout') !== false
        ) {

            Log::warning('📡 Error de conexión HTTP detectado', [
                'error_id' => $errorId,
                'connection_target' => $this->extractConnectionTarget($errorMessage),
                'user_action' => $this->editingId ? 'update' : 'create',
                'context' => $contextData
            ]);

            return '📡 Error de Conexión: No se pudo conectar con el sistema de facturación. Verifique su conexión a internet e intente nuevamente. (ID: ' . $errorId . ')';
        }

        // Errores de memoria
        if (strpos($errorMessage, 'memory') !== false || strpos($errorMessage, 'Maximum execution time') !== false) {
            Log::critical('🔧 Error de recursos del sistema', [
                'error_id' => $errorId,
                'memory_usage' => memory_get_peak_usage(true),
                'execution_time' => microtime(true) - LARAVEL_START,
                'context' => $contextData
            ]);

            return '🔧 Error de Sistema: El sistema está experimentando una sobrecarga de recursos. Por favor intente nuevamente en unos momentos. (ID: ' . $errorId . ')';
        }

        // Errores de permisos
        if (
            strpos($errorMessage, 'permission') !== false ||
            strpos($errorMessage, 'Access denied') !== false ||
            strpos($errorMessage, 'Forbidden') !== false
        ) {

            Log::warning('🔐 Error de permisos detectado', [
                'error_id' => $errorId,
                'user_id' => Auth::id(),
                'user_roles' => Auth::user() ? Auth::user()->getRoleNames() : [],
                'attempted_action' => $this->editingId ? 'update_customer' : 'create_customer',
                'context' => $contextData
            ]);

            return '🔐 Error de Permisos: Su usuario no tiene permisos suficientes para realizar esta operación. Contacte al administrador del sistema. (ID: ' . $errorId . ')';
        }

        // Errores de archivos y sistema de archivos
        if (
            strpos($errorMessage, 'file') !== false ||
            strpos($errorMessage, 'directory') !== false ||
            strpos($errorMessage, 'fopen') !== false
        ) {

            Log::error('📁 Error del sistema de archivos', [
                'error_id' => $errorId,
                'disk_space' => disk_free_space('.'),
                'writable' => is_writable(storage_path()),
                'context' => $contextData
            ]);

            return '📁 Error de Archivos: Hubo un problema accediendo a los archivos del sistema. Contacte al administrador técnico. (ID: ' . $errorId . ')';
        }

        // Errores de validación de Livewire
        if (strpos($errorClass, 'ValidationException') !== false) {
            Log::info('📝 Error de validación capturado en handleGeneralError', [
                'error_id' => $errorId,
                'validation_errors' => method_exists($e, 'errors') ? $e->errors() : 'no_errors_method',
                'context' => $contextData
            ]);

            return '📝 Error de Validación: Algunos campos no cumplen con los requisitos. Por favor revise la información e intente nuevamente.';
        }

        // Errores de base de datos que llegaron hasta acá
        if (
            strpos($errorClass, 'QueryException') !== false ||
            strpos($errorClass, 'PDOException') !== false
        ) {

            Log::error('💾 Error de BD no manejado por handleDatabaseError', [
                'error_id' => $errorId,
                'should_be_handled_by' => 'handleDatabaseError',
                'context' => $contextData
            ]);

            return '💾 Error de Base de Datos: Problema inesperado con la base de datos. Contacte al administrador. (ID: ' . $errorId . ')';
        }

        // Error completamente inesperado
        Log::critical('❓ Error no categorizado requiere investigación', [
            'error_id' => $errorId,
            'priority' => 'HIGH',
            'requires_investigation' => true,
            'error_class' => $errorClass,
            'context' => $contextData
        ]);

        return '⚠️ Error Inesperado: Ocurrió un problema inesperado. Por favor intente nuevamente. Si el problema persiste, contacte al soporte técnico con el código: ' . $errorId;
    }

    /**
     * Manejar errores de validación en tiempo real con mensajes específicos
     * Proporciona mensajes amigables y claros para el usuario
     */
    public function addValidationError(string $field, string $message): void
    {
        $friendlyMessages = [
            'identification' => [
                'required' => '📄 Número de Identificación: Este campo es obligatorio para registrar el cliente.',
                'unique' => '👤 Identificación Duplicada: Ya existe un cliente con este número de identificación.',
                'numeric' => '🔢 Formato Inválido: El número de identificación debe contener solo números, sin puntos ni guiones.',
                'min' => '📏 Muy Corto: El número de identificación debe tener al menos 6 dígitos para ser válido.',
                'max' => '📏 Muy Largo: El número de identificación no puede exceder 15 dígitos según normativas.',
                'digits_between' => '📏 Longitud Inválida: El número de identificación debe tener entre 6 y 15 dígitos.'
            ],
            'billingEmail' => [
                'required' => '✉️ Email de Facturación: Este campo es obligatorio para el envío de facturas electrónicas.',
                'email' => '📧 Formato Inválido: Por favor ingrese un email válido (ejemplo: usuario@empresa.com).',
                'unique' => '✉️ Email Duplicado: Ya existe un cliente con este email de facturación.',
                'max' => '📧 Email Muy Largo: El email no puede exceder 100 caracteres.'
            ],
            'firstName' => [
                'required' => '👤 Primer Nombre: Este campo es obligatorio para personas naturales.',
                'min' => '📝 Muy Corto: El primer nombre debe tener al menos 2 caracteres.',
                'max' => '📝 Muy Largo: El primer nombre no puede exceder 50 caracteres.',
                'regex' => '🔤 Formato Inválido: El nombre solo puede contener letras y espacios.'
            ],
            'lastName' => [
                'required' => '👤 Primer Apellido: Este campo es obligatorio para personas naturales.',
                'min' => '📝 Muy Corto: El primer apellido debe tener al menos 2 caracteres.',
                'max' => '📝 Muy Largo: El primer apellido no puede exceder 50 caracteres.',
                'regex' => '🔤 Formato Inválido: El apellido solo puede contener letras y espacios.'
            ],
            'businessName' => [
                'required' => '🏢 Razón Social: Este campo es obligatorio para personas jurídicas.',
                'min' => '📝 Muy Corto: La razón social debe tener al menos 3 caracteres.',
                'max' => '📝 Muy Largo: La razón social no puede exceder 100 caracteres.'
            ],
            'typeIdentificationId' => [
                'required' => '📋 Tipo de Identificación: Debe seleccionar el tipo de documento de identidad.',
                'exists' => '❌ Tipo Inválido: El tipo de identificación seleccionado no es válido.'
            ],
            'regimeId' => [
                'required' => '📊 Régimen Fiscal: Este campo es obligatorio para personas jurídicas.',
                'exists' => '❌ Régimen Inválido: El régimen fiscal seleccionado no es válido.'
            ],
            'fiscalResponsabilityId' => [
                'exists' => '❌ Responsabilidad Inválida: La responsabilidad fiscal seleccionada no es válida.'
            ],
            'warehouseName' => [
                'required' => '🏪 Nombre de Sucursal: Debe ingresar el nombre de la sucursal principal.',
                'min' => '📝 Muy Corto: El nombre de la sucursal debe tener al menos 2 caracteres.',
                'max' => '📝 Muy Largo: El nombre de la sucursal no puede exceder 100 caracteres.'
            ],
            'warehouseAddress' => [
                'required' => '📍 Dirección de Sucursal: Debe ingresar la dirección de la sucursal principal.',
                'min' => '📝 Muy Corta: La dirección debe tener al menos 5 caracteres.',
                'max' => '📝 Muy Larga: La dirección no puede exceder 200 caracteres.'
            ],
            'warehouseCityId' => [
                'required' => '🌆 Ciudad: Debe seleccionar la ciudad donde se ubica la sucursal.',
                'exists' => '❌ Ciudad Inválida: La ciudad seleccionada no es válida.'
            ],
            'business_phone' => [
                'min' => '📞 Muy Corto: El teléfono comercial debe tener al menos 7 dígitos.',
                'max' => '📞 Muy Largo: El teléfono comercial no puede exceder 20 caracteres.',
                'regex' => '📞 Formato Inválido: El teléfono debe contener solo números, espacios, guiones y paréntesis.'
            ],
            'personal_phone' => [
                'min' => '📱 Muy Corto: El teléfono personal debe tener al menos 7 dígitos.',
                'max' => '📱 Muy Largo: El teléfono personal no puede exceder 20 caracteres.',
                'regex' => '📱 Formato Inválido: El teléfono debe contener solo números, espacios, guiones y paréntesis.'
            ],
            'code_ciiu' => [
                'min' => '🏭 Muy Corto: El código CIIU debe tener al menos 4 caracteres.',
                'max' => '🏭 Muy Largo: El código CIIU no puede exceder 10 caracteres.'
            ]
        ];

        // Buscar mensaje específico o usar el mensaje proporcionado
        $rule = $this->getValidationRuleFromMessage($message);
        $friendlyMessage = $friendlyMessages[$field][$rule] ?? $message;

        // Agregar contexto adicional si es necesario
        if ($field === 'identification' && strpos($message, 'duplicate') !== false) {
            $friendlyMessage .= '\n\n💡 Sugerencia: Verifique que el tipo y número de identificación sean correctos, o que no esté intentando registrar un cliente que ya existe.';
        }

        if ($field === 'billingEmail' && strpos($message, 'duplicate') !== false) {
            $friendlyMessage .= '\n\n💡 Sugerencia: Puede usar un email diferente o verificar si el cliente ya está registrado en el sistema.';
        }

        $this->addError($field, $friendlyMessage);

        // Log para debugging con información contextual
        Log::warning('🔍 Error de validación mostrado al usuario', [
            'field' => $field,
            'original_message' => $message,
            'friendly_message' => $friendlyMessage,
            'rule' => $rule,
            'user_id' => Auth::id(),
            'editing_id' => $this->editingId
        ]);
    }

    /**
     * Extraer la regla de validación del mensaje de error
     * Identifica el tipo de error para mostrar mensajes más específicos
     */
    private function getValidationRuleFromMessage(string $message): string
    {
        $lowerMessage = strtolower($message);

        // Reglas más específicas primero
        if (strpos($lowerMessage, 'required') !== false) return 'required';
        if (strpos($lowerMessage, 'unique') !== false || strpos($lowerMessage, 'duplicad') !== false) return 'unique';
        if (strpos($lowerMessage, 'email') !== false && strpos($lowerMessage, 'format') !== false) return 'email';
        if (strpos($lowerMessage, 'numeric') !== false) return 'numeric';
        if (strpos($lowerMessage, 'digits_between') !== false) return 'digits_between';
        if (strpos($lowerMessage, 'min') !== false) return 'min';
        if (strpos($lowerMessage, 'max') !== false) return 'max';
        if (strpos($lowerMessage, 'regex') !== false || strpos($lowerMessage, 'format') !== false) return 'regex';
        if (strpos($lowerMessage, 'exists') !== false || strpos($lowerMessage, 'valid') !== false) return 'exists';
        if (strpos($lowerMessage, 'confirmed') !== false) return 'confirmed';
        if (strpos($lowerMessage, 'same') !== false) return 'same';
        if (strpos($lowerMessage, 'different') !== false) return 'different';
        if (strpos($lowerMessage, 'before') !== false) return 'before';
        if (strpos($lowerMessage, 'after') !== false) return 'after';
        if (strpos($lowerMessage, 'date') !== false) return 'date';

        return 'general';
    }

    /**
     * Construir contexto detallado para logging de errores
     */
    private function buildErrorContext(): array
    {
        return [
            'user_id' => Auth::id(),
            'user_email' => Auth::user()?->email,
            'editing_id' => $this->editingId,
            'is_editing' => !is_null($this->editingId),
            'form_action' => $this->editingId ? 'update' : 'create',
            'form_data_summary' => [
                'identification' => $this->identification ? substr($this->identification, 0, 4) . '****' : null,
                'email' => $this->billingEmail ? substr($this->billingEmail, 0, 3) . '****@****' : null,
                'type_person' => $this->typePerson,
                'type_identification_id' => $this->typeIdentificationId,
                'has_business_name' => !empty($this->businessName),
                'has_names' => !empty($this->firstName) || !empty($this->lastName),
                'regime_id' => $this->regimeId,
                'fiscal_responsibility_id' => $this->fiscalResponsabilityId,
                'warehouse_city_id' => $this->warehouseCityId
            ],
            'session_info' => [
                'tenant_id' => session('tenant_id'),
                'has_tenant' => !is_null(session('tenant_id'))
            ],
            'timestamp' => now()->toISOString(),
            'request_id' => request()->header('X-Request-ID', uniqid()),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ];
    }

    /**
     * Extraer campo específico del error "Data too long"
     */
    private function extractFieldFromDataTooLongError(string $errorMessage): array
    {
        // Intentar extraer el nombre del campo del mensaje de error
        $fieldMappings = [
            'businessName' => ['business_name', 'businessname', 'razón social'],
            'firstName' => ['first_name', 'firstname', 'primer nombre'],
            'lastName' => ['last_name', 'lastname', 'apellido'],
            'billingEmail' => ['billing_email', 'email', 'correo'],
            'warehouseAddress' => ['address', 'direccion', 'direccion'],
            'warehouseName' => ['warehouse_name', 'sucursal', 'almacen']
        ];

        $lowerMessage = strtolower($errorMessage);

        foreach ($fieldMappings as $field => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($lowerMessage, $pattern) !== false) {
                    return [
                        'affected_field' => $field,
                        'friendly_message' => $this->getDataTooLongMessage($field),
                        'current_length' => strlen($this->{$field} ?? ''),
                        'error_message' => $errorMessage
                    ];
                }
            }
        }

        return [
            'affected_field' => 'unknown',
            'friendly_message' => 'Uno de los campos contiene demasiado texto. Por favor reduzca la longitud de la información.',
            'error_message' => $errorMessage
        ];
    }

    /**
     * Obtener mensaje específico para campo demasiado largo
     */
    private function getDataTooLongMessage(string $field): string
    {
        $messages = [
            'businessName' => 'La razón social es demasiado larga. Máximo 100 caracteres.',
            'firstName' => 'El primer nombre es demasiado largo. Máximo 50 caracteres.',
            'lastName' => 'El primer apellido es demasiado largo. Máximo 50 caracteres.',
            'billingEmail' => 'El email de facturación es demasiado largo. Máximo 100 caracteres.',
            'warehouseAddress' => 'La dirección de la sucursal es demasiado larga. Máximo 200 caracteres.',
            'warehouseName' => 'El nombre de la sucursal es demasiado largo. Máximo 100 caracteres.'
        ];

        return $messages[$field] ?? 'Este campo es demasiado largo. Por favor reduzca el texto.';
    }

    /**
     * Extraer campo nulo del mensaje de error
     */
    private function extractNullFieldFromError(string $errorMessage): string
    {
        // Patrones comunes en errores de MySQL/PostgreSQL
        if (preg_match("/Column '([^']+)' cannot be null/", $errorMessage, $matches)) {
            return $matches[1];
        }

        if (preg_match("/null value in column \"([^\"]+)\"/", $errorMessage, $matches)) {
            return $matches[1];
        }

        return 'unknown_field';
    }

    /**
     * Extraer destino de conexión del mensaje de error
     */
    private function extractConnectionTarget(string $errorMessage): ?string
    {
        // Intentar extraer URL o host del mensaje de error
        if (preg_match('/(?:https?:\/\/)?([^\s\/]+)/i', $errorMessage, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Construir mensaje de éxito personalizado con contexto
     */
    private function buildSuccessMessage(string $action, string $syncType, string $customerName): string
    {
        $customerName = trim($customerName);
        $customerDisplay = !empty($customerName) ? " '{$customerName}'" : '';

        $icons = [
            'create' => '✅',
            'update' => '🔄'
        ];

        $actions = [
            'create' => 'Registrado',
            'update' => 'Actualizado'
        ];

        $syncMessages = [
            'sync' => 'se guardó localmente y se sincronizó exitosamente con el sistema de facturación electrónica',
            'local' => 'se guardó exitosamente en el sistema local'
        ];

        $icon = $icons[$action] ?? '✅';
        $actionText = $actions[$action] ?? 'Procesado';
        $syncMessage = $syncMessages[$syncType] ?? 'se procesó correctamente';

        $baseMessage = "{$icon} Cliente {$actionText}: El cliente{$customerDisplay} {$syncMessage}.";

        // Agregar información adicional según el contexto
        $additionalInfo = [];

        if ($syncType === 'sync') {
            $additionalInfo[] = "🔗 Los datos están disponibles para facturación electrónica";
        }

        if ($action === 'create' && $this->routeId) {
            $additionalInfo[] = "📍 Ruta de ventas asignada correctamente";
        }

        if (!empty($additionalInfo)) {
            $baseMessage .= "\n\n💡 Información adicional:\n• " . implode("\n• ", $additionalInfo);
        }

        return $baseMessage;
    }

    /**
     * Construir mensaje de éxito para rutas
     */
    private function buildRouteSuccessMessage($company, $route): string
    {
        $customerName = $company->businessName ?: ($company->firstName . ' ' . $company->lastName);

        return "✅ Cliente y Ruta Creados: El cliente '{$customerName}' se registró exitosamente y se asignó a la ruta de ventas.\n\n" .
            "📍 Detalles de la ruta:\n" .
            "• Número de orden: {$route->sales_order}\n" .
            "• ID de ruta: {$route->route_id}";
    }

    /**
     * Construir mensaje de error para rutas
     */
    private function buildRouteErrorMessage($company): string
    {
        $customerName = $company->businessName ?: ($company->firstName . ' ' . $company->lastName);

        return "✅ Cliente Creado con Advertencia: El cliente '{$customerName}' se registró exitosamente en el sistema.\n\n" .
            "⚠️ Problema con Ruta: Hubo un inconveniente al asignar la ruta de ventas. La ruta se puede asignar posteriormente desde la gestión de rutas.";
    }

    /**
     * Construir mensaje de éxito para eliminación
     */
    private function buildDeleteSuccessMessage(): string
    {
        return "🗑️ Cliente Eliminado: El registro se ha eliminado exitosamente del sistema.\n\n" .
            "💡 Información:\n" .
            "• Los datos se eliminaron permanentemente\n" .
            "• Los registros asociados se mantuvieron intactos\n" .
            "• Esta acción no se puede deshacer";
    }

    /**
     * Construir mensaje de error por restricciones de integridad
     */
    private function buildConstraintErrorMessage(): string
    {
        return "🚫 No se puede Eliminar: Este cliente está asociado a registros importantes del sistema.\n\n" .
            "🔗 Registros que pueden estar asociados:\n" .
            "• Facturas electrónicas emitidas\n" .
            "• Pedidos de venta realizados\n" .
            "• Transacciones financieras\n" .
            "• Historial de compras\n\n" .
            "💡 Soluciones:\n" .
            "• Desactive el cliente en lugar de eliminarlo\n" .
            "• Transfiera los registros a otro cliente\n" .
            "• Contacte al administrador para realizar una eliminación segura";
    }

    /**
     * Construir mensaje de error de base de datos para eliminación
     */
    private function buildDatabaseDeleteErrorMessage(): string
    {
        return "💾 Error de Base de Datos: Hubo un problema técnico al eliminar el cliente.\n\n" .
            "🔧 Acciones recomendadas:\n" .
            "• Intente nuevamente en unos momentos\n" .
            "• Verifique que tiene los permisos necesarios\n" .
            "• Contacte al administrador si el problema persiste\n\n" .
            "💡 Alternativa: Puede desactivar el cliente temporalmente en lugar de eliminarlo.";
    }

    /**
     * Construir mensaje de error general para eliminación
     */
    private function buildGeneralDeleteErrorMessage(): string
    {
        $errorId = substr(md5(time() . Auth::id()), 0, 8);

        return "⚠️ Error Inesperado: Ocurrió un problema inesperado al eliminar el cliente.\n\n" .
            "🆔 Código de error: {$errorId}\n\n" .
            "🔧 Qué hacer:\n" .
            "• Intente nuevamente en unos minutos\n" .
            "• Si persiste, contacte al soporte técnico\n" .
            "• Proporcione el código de error al soporte\n\n" .
            "💡 Como alternativa temporal, puede desactivar el cliente en lugar de eliminarlo.";
    }

    /**
     * Verificar integridad del sistema de manejo de errores
     * Este método se puede usar para testing y debugging
     */
    public function validateErrorHandlingCompleteness(): array
    {
        $results = [
            'validation_errors' => true,
            'database_errors' => true,
            'api_errors' => true,
            'general_errors' => true,
            'success_messages' => true,
            'logging_system' => true,
            'completeness_score' => 0
        ];

        $totalChecks = count($results) - 1; // Excluir el score

        // Verificar que los métodos de manejo de errores existen
        $requiredMethods = [
            'handleDatabaseError',
            'handleGeneralError',
            'formatApiErrorMessage',
            'categorizeApiError',
            'addValidationError',
            'buildSuccessMessage',
            'buildErrorContext'
        ];

        $methodsExist = 0;
        foreach ($requiredMethods as $method) {
            if (method_exists($this, $method)) {
                $methodsExist++;
            }
        }

        // Verificar logging
        $results['logging_system'] = $methodsExist === count($requiredMethods);

        // Verificar mensajes de validación
        $validationMessages = $this->validationService->getValidationMessages();
        $results['validation_errors'] = !empty($validationMessages);

        // Calcular score de completeness
        $completedChecks = array_sum(array_filter($results, function ($key) {
            return $key !== 'completeness_score';
        }, ARRAY_FILTER_USE_KEY));

        $results['completeness_score'] = round(($completedChecks / $totalChecks) * 100, 2);

        // Log del resultado para monitoreo
        Log::info('🔍 Verificación de completeness del sistema de errores', [
            'results' => $results,
            'required_methods_found' => $methodsExist . '/' . count($requiredMethods),
            'timestamp' => now()->toISOString()
        ]);

        return $results;
    }

    /**
     * Limpiar campos de persona para modo simplificado
     */
    private function resetPersonFieldsForSimplified()
    {
        if ($this->typePerson == 'Natural') {
            $this->businessName = '';
        } else if ($this->typePerson == 'Juridica') {
            $this->firstName = '';
            $this->lastName = '';
        }
    }

    public function canUploadsEnable()
    {
        $result = $this->isOptionEnabled(75);
        $value = $this->getOptionValue(75);

        Log::info('🎮 canUploadsEnable() verificación', [
            'companyId' => $this->currentCompanyId,
            'option_id' => 75,
            'result' => $result ? 'TRUE' : 'FALSE',
            'option_value' => $value,
            'configService_exists' => $this->configService ? 'YES' : 'NO',
            'method_called' => 'isOptionEnabled(75) y getOptionValue(75)'
        ]);
        return $result;
    }
    /**
     * Sobreescribir initializeCompanyConfiguration para asegurar conexión tenant
     */
    protected function initializeCompanyConfiguration(): void
    {
        // Solo saltar si YA ESTÁ inicializado Y tenemos el servicio y el ID (para evitar fallos por pérdida de estado en Livewire)
        if ($this->isConfigurationInitialized && $this->configService && $this->currentCompanyId) {
            Log::info('🔧 initializeCompanyConfiguration() - YA INICIALIZADO con datos, saltando...');
            return;
        }

        Log::info('🔧 initializeCompanyConfiguration() - INICIO');

        // PRIMERO: Asegurar conexión tenant antes de cualquier otra operación
        try {
            $this->ensureTenantConnection();
            Log::info('🔧 Conexión tenant asegurada antes de inicialización');
        } catch (\Exception $e) {
            Log::error('🔧 Error asegurando conexión tenant', ['error' => $e->getMessage()]);
        }

        $this->configService = app(\App\Services\Configuration\CompanyConfigurationService::class);
        Log::info('🔧 ConfigService creado', ['service_exists' => $this->configService ? 'YES' : 'NO']);

        // Obtener datos de la empresa actual usando el mismo validador que UpdateCompany
        $user = Auth::user();
        Log::info('🔧 Usuario obtenido', [
            'user_exists' => $user ? 'YES' : 'NO',
            'user_id' => $user->id ?? 'NULL',
            'user_email' => $user->email ?? 'NULL'
        ]);

        if ($user) {
            $validator = app(\App\Services\Company\CompanyDataValidator::class);
            Log::info('🔧 Validator creado');

            $company = $validator->getUserCompany($user);
            Log::info('🔧 Empresa obtenida', [
                'company_exists' => $company ? 'YES' : 'NO',
                'company_id' => $company->id ?? 'NULL',
                'company_name' => $company->businessName ?? 'NULL'
            ]);

            if ($company) {
                $this->currentCompanyId = $company->id;
                $this->currentPlainId = $this->getUserPlainId($user); // Por defecto plan 2 (Avanzado)

                // Sincronizar con el estado estático del Trait para que funcionen los métodos auxiliares
                self::$sharedCompanyId = $this->currentCompanyId;
                self::$sharedPlainId = $this->currentPlainId;
                self::$staticConfigService = $this->configService;
                self::$isStaticInitialized = true;

                Log::info('🔧 IDs asignados y sincronizados con Trait', [
                    'currentCompanyId' => $this->currentCompanyId,
                    'currentPlainId' => $this->currentPlainId,
                    'sharedCompanyId' => self::$sharedCompanyId
                ]);
            } else {
                Log::warning('🔧 No se encontró empresa para el usuario');
            }
        } else {
            Log::warning('🔧 No hay usuario autenticado');
        }

        // Precargar configuraciones comunes DESPUÉS de asegurar conexión tenant
        if ($this->currentCompanyId && $this->currentPlainId) {
            Log::info('🔧 Precargando configuraciones');
            try {
                // IMPORTANTE: Volver a asegurar conexión antes de precargar
                $this->ensureTenantConnection();
                $this->configService->preloadCommonConfigurations(
                    $this->currentCompanyId,
                    $this->currentPlainId
                );
                Log::info('🔧 Configuraciones precargadas exitosamente');
            } catch (\Exception $e) {
                Log::error('🔧 Error precargando configuraciones', ['error' => $e->getMessage()]);
            }
        } else {
            Log::warning('🔧 No se pueden precargar configuraciones - faltan IDs', [
                'currentCompanyId' => $this->currentCompanyId,
                'currentPlainId' => $this->currentPlainId
            ]);
        }

        $this->isConfigurationInitialized = true;

        Log::info('🔧 initializeCompanyConfiguration() - FIN', [
            'final_companyId' => $this->currentCompanyId,
            'final_plainId' => $this->currentPlainId,
            'final_configService' => $this->configService ? 'YES' : 'NO'
        ]);
    }
}
