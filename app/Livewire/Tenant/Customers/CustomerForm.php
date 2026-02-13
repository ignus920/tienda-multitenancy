<?php

namespace App\Livewire\Tenant\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Customer\VntCompany;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasCompanyConfiguration;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

// Sincronización con el API
use App\Services\Facturacion\DatabaseConfigService;
use App\Services\Facturacion\ApiClient;

class CustomerForm extends Component
{
    use WithPagination, HasCompanyConfiguration;

    // Pagination and search
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    // Modal control
    public $showModal = false;
    public $editingId = null;

    // Form properties - Basic info
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';

    // Form properties - Business info
    public $business_name = '';
    public $identification_number = '';
    public $identification_type = '';
    public $dv = '';
    public $kind_of_person = 'LEGAL_ENTITY'; // LEGAL_ENTITY or NATURAL_PERSON
    public $regime = '';
    public $fiscal_responsibilities = [];

    // Form properties - Location
    public $country_id = null;
    public $state_id = null;
    public $city_id = null;
    public $postcode = '';

    // Form properties - Other
    public $type = 'client';
    public $active = true;

    // Message properties
    public $successMessage = '';
    public $errorMessage = '';

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($this->editingId)
            ],
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'business_name' => 'nullable|string|max:255',
            'identification_number' => 'required|string|max:50',
            'identification_type' => 'required|string|max:10',
            'dv' => 'nullable|string|max:2',
            'kind_of_person' => 'required|in:LEGAL_ENTITY,NATURAL_PERSON',
            'regime' => 'required|string|max:50',
            'fiscal_responsibilities' => 'nullable|array',
            'country_id' => 'nullable|integer',
            'state_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'postcode' => 'nullable|string|max:20',
            'type' => 'required|string|max:50',
            'active' => 'boolean',
        ];

        return $rules;
    }

    /**
     * Custom validation messages in Spanish
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe ser válido',
            'email.unique' => 'Este email ya está registrado',
            'phone.required' => 'El teléfono es obligatorio',
            'address.required' => 'La dirección es obligatoria',
            'identification_number.required' => 'El número de identificación es obligatorio',
            'identification_type.required' => 'El tipo de identificación es obligatorio',
            'kind_of_person.required' => 'Debe seleccionar el tipo de persona',
            'regime.required' => 'El régimen es obligatorio',
        ];
    }

    public function render()
    {
        return view('livewire.tenant.customers.customer-form', [
            'customers' => $this->customersProperty
        ]);
    }

    /**
     * Computed property to get customers with pagination
     */
    public function getCustomersProperty()
    {
        // Log para verificar qué conexión está usando Customer
        $customerConnection = Customer::getConnectionName();

        try {
            $databaseName = DB::connection($customerConnection)->getDatabaseName();
            Log::info('🔍 CONSULTA CUSTOMERS - Verificando BD', [
                'model' => 'Customer',
                'connection_name' => $customerConnection,
                'database_name' => $databaseName,
                'search_term' => $this->search,
                'method' => 'CustomerForm::getCustomersProperty'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ ERROR AL OBTENER NOMBRE DE BD EN CUSTOMERS', [
                'connection_name' => $customerConnection,
                'error' => $e->getMessage(),
                'method' => 'CustomerForm::getCustomersProperty'
            ]);
        }

        return Customer::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('business_name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function create()
    {
        $this->resetForm();
        $this->resetErrorBag();
        $this->resetValidation();

        $this->successMessage = '';
        $this->errorMessage = '';
        $this->editingId = null;

        $this->showModal = true;
    }

    public function edit(int $customerId)
    {
        $this->resetForm();
        $this->resetErrorBag();
        $this->resetValidation();
        $this->successMessage = '';
        $this->errorMessage = '';

        $customer = Customer::findOrFail($customerId);

        $this->editingId = $customerId;
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
        $this->business_name = $customer->business_name;
        $this->identification_number = $customer->identification_number;
        $this->identification_type = $customer->identification_type;
        $this->dv = $customer->dv;
        $this->kind_of_person = $customer->kind_of_person ?? 'LEGAL_ENTITY';
        $this->regime = $customer->regime;
        $this->fiscal_responsibilities = $customer->fiscal_responsibilities ?? [];
        $this->country_id = $customer->country_id;
        $this->state_id = $customer->state_id;
        $this->city_id = $customer->city_id;
        $this->postcode = $customer->postcode;
        $this->type = $customer->type ?? 'client';
        $this->active = $customer->active ?? true;

        $this->showModal = true;
    }

    /**
     * Close modal and reset form
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->successMessage = '';
        $this->errorMessage = '';

        $this->resetErrorBag();
        $this->resetValidation();
    }

    /**
     * Save customer (create or update)
     */
    public function save(): void
    {
        Log::info('🚀 CustomerForm::save() INICIADO');

        try {
            // ✅ PRIMER PASO: Asegurar conexión tenant ANTES de cualquier validación
            Log::info('🔗 Asegurando conexión tenant al inicio de save()');
            $this->ensureTenantConnection();

            // Clear previous messages
            $this->errorMessage = '';
            $this->successMessage = '';

            Log::info('🔍 Datos del formulario', [
                'name' => $this->name,
                'email' => $this->email,
                'identification_number' => $this->identification_number,
                'editingId' => $this->editingId
            ]);

            // Validate all inputs
            Log::info('🔍 Iniciando validación de formulario');
            $this->validate($this->rules(), $this->messages());
            Log::info('✅ Validación de formulario exitosa');

            // PASO 1: Validar que es posible sincronizar con la API ANTES de guardar
            Log::info('🔍 Iniciando preValidateApiSync (conexión tenant ya establecida)');
            $preValidationResult = $this->preValidateApiSync();
            if (!$preValidationResult['success']) {
                // Cerrar modal y mostrar error con session flash para mayor visibilidad
                $this->closeModal();
                session()->flash('sync_error', $preValidationResult['message']);
                return;
            }

            // PASO 2: Preparar datos de API ANTES de crear customer para validar duplicados
            $tempApiData = $this->prepareApiData();

            // PASO 3: Validar datos con API (puede crear temporalmente para validar)
            $apiValidationResult = $this->validateApiData($tempApiData);
            if (!$apiValidationResult['success']) {
                // Cerrar modal y mostrar error con session flash para mayor visibilidad
                $this->closeModal();
                session()->flash('sync_error', $apiValidationResult['message']);
                return;
            }

            // Verificar si la validación ya creó un registro temporal en la API
            $tempApiId = $apiValidationResult['temp_api_id'] ?? null;

            $customer = null;

            // PASO 4: Guardar en base de datos local solo si API validó correctamente
            if ($this->editingId) {
                // Update mode
                $customer = Customer::findOrFail($this->editingId);
                $this->updateCustomer($customer);
            } else {
                // Create mode
                $customer = $this->createCustomer();
            }

            // PASO 5: Finalizar sincronización con API
            if ($customer) {
                try {
                    set_time_limit(45); // Aumentar timeout para sincronización API

                    // Si ya tenemos temp_api_id, solo necesitamos asignarlo al customer
                    if ($tempApiId && !$this->editingId) {
                        Log::info('✅ Usando API ID temporal de validación', [
                            'customer_id' => $customer->id,
                            'temp_api_id' => $tempApiId
                        ]);

                        $customer->update(['api_data_id' => $tempApiId]);
                        $syncResult = [
                            'success' => true,
                            'message' => 'Cliente sincronizado usando validación previa'
                        ];
                    } else {
                        // Sincronización normal (para updates o cuando no hay temp_api_id)
                        $syncResult = $this->syncCustomerWithApi($customer);
                    }

                    set_time_limit(60); // Restaurar timeout normal

                    if ($syncResult['success']) {
                        session()->flash('sync_message', '✅ Cliente Sincronizado: El cliente ha sido creado/actualizado exitosamente y sincronizado con la API de facturación electrónica.');
                    } else {
                        // Si falla sincronización después de validar, eliminar customer creado
                        if (!$this->editingId) {
                            $customer->delete();
                        }
                        $this->closeModal();
                        session()->flash('sync_error', '❌ Error de Sincronización API: ' . $syncResult['message']);
                        return;
                    }
                } catch (\Exception $e) {
                    set_time_limit(60); // Restaurar timeout normal
                    // Si falla sincronización después de validar, eliminar customer creado
                    if (!$this->editingId) {
                        $customer->delete();
                    }

                    Log::error('Error en sincronización post-validación', [
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage()
                    ]);
                    $this->closeModal();
                    session()->flash('sync_error', '❌ Error de Conexión API: Falló la comunicación con la API de facturación. Error: ' . $e->getMessage());
                    return;
                }
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors are automatically handled by Livewire
            Log::info('Validation error in save method', [
                'errors' => $e->errors(),
            ]);
        } catch (\Exception $e) {
            $this->errorMessage = 'Error inesperado: ' . $e->getMessage();
            Log::error('Unexpected error in save method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function delete($id)
    {
        try {
            Customer::findOrFail($id)->delete();
            session()->flash('message', 'Cliente eliminado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el cliente: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->business_name = '';
        $this->identification_number = '';
        $this->identification_type = '';
        $this->dv = '';
        $this->kind_of_person = 'LEGAL_ENTITY';
        $this->regime = '';
        $this->fiscal_responsibilities = [];
        $this->country_id = null;
        $this->state_id = null;
        $this->city_id = null;
        $this->postcode = '';
        $this->type = 'client';
        $this->active = true;
    }

    /**
     * Create customer with all data
     */
    private function createCustomer(): Customer
    {
        try {
            DB::beginTransaction();

            $customer = Customer::create([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'business_name' => $this->business_name,
                'identification_number' => $this->identification_number,
                'identification_type' => $this->identification_type,
                'dv' => $this->dv,
                'kind_of_person' => $this->kind_of_person,
                'regime' => $this->regime,
                'fiscal_responsibilities' => $this->fiscal_responsibilities,
                'country_id' => $this->country_id,
                'state_id' => $this->state_id,
                'city_id' => $this->city_id,
                'postcode' => $this->postcode,
                'type' => $this->type,
                'active' => $this->active,
            ]);

            DB::commit();

            // Set success message before closing
            $this->successMessage = 'Cliente creado exitosamente';
            $this->errorMessage = '';

            // Close modal after successful creation
            $this->closeModal();

            return $customer;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Error al crear el cliente: ' . $e->getMessage();

            Log::error('Customer creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'email' => $this->email,
                    'identification_number' => $this->identification_number,
                ]
            ]);

            throw $e;
        }
    }

    /**
     * Update customer with all data
     */
    private function updateCustomer(Customer $customer): Customer
    {
        try {
            DB::beginTransaction();

            $customer->update([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'address' => $this->address,
                'business_name' => $this->business_name,
                'identification_number' => $this->identification_number,
                'identification_type' => $this->identification_type,
                'dv' => $this->dv,
                'kind_of_person' => $this->kind_of_person,
                'regime' => $this->regime,
                'fiscal_responsibilities' => $this->fiscal_responsibilities,
                'country_id' => $this->country_id,
                'state_id' => $this->state_id,
                'city_id' => $this->city_id,
                'postcode' => $this->postcode,
                'type' => $this->type,
                'active' => $this->active,
            ]);

            DB::commit();

            // Set success message before closing
            $this->successMessage = 'Cliente actualizado exitosamente';
            $this->errorMessage = '';

            // Close modal after successful update
            $this->closeModal();

            return $customer;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Error al actualizar el cliente: ' . $e->getMessage();

            Log::error('Customer update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'customer_id' => $customer->id,
                    'email' => $this->email,
                ]
            ]);

            throw $e;
        }
    }

    /**
     * Pre-validar que la sincronización con API es posible ANTES de guardar
     */
    private function preValidateApiSync(): array
    {
        try {
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

            // Verificar si facturación electrónica está habilitada
            $isElectronicEnabled = $this->isElectronicInvoicingEnabled($this->currentCompanyId);
            if (!$isElectronicEnabled) {
                return [
                    'success' => false,
                    'message' => '⚠️ Facturación Electrónica Deshabilitada: Para sincronizar clientes con la API de facturación, debe activar el módulo de facturación electrónica en la configuración de la empresa.'
                ];
            }

            // Verificar límites del plan para clientes (usar opción específica para clientes o la misma de usuarios)
            if (!$this->canSyncClients()) {
                return [
                    'success' => false,
                    'message' => '📊 Límite del Plan Alcanzado: Ha alcanzado el máximo número de clientes permitidos en su plan actual. Para sincronizar más clientes, considere actualizar su plan o contacte al administrador.'
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

            // Verificar configuración de API (conexión tenant ya establecida)
            $optimizedConfig = DatabaseConfigService::getFacturacionConfigByUser($authUser->id);
            if (!$optimizedConfig) {
                return [
                    'success' => false,
                    'message' => '⚙️ Error de Configuración API: No se encontró la configuración de facturación electrónica para sincronizar clientes. Verifique que la configuración esté correctamente establecida en el módulo de facturación o contacte al administrador.'
                ];
            }

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
     * Preparar datos para la API basado en el JSON proporcionado
     */
    private function prepareApiData(): array
    {
        // Preparar nameObject si no es entidad legal
        $nameObject = null;
        if ($this->kind_of_person !== 'LEGAL_ENTITY') {
            $nameObject = [
                'firstName' => $this->name, // Asumir que name contiene el nombre completo
                'lastName' => $this->name   // Se podría separar si es necesario
            ];
        }

        // Preparar city info (esto debería venir de una consulta a la BD de ciudades)
        $cityInfo = $this->getCityInfo($this->city_id);

        return [
            'nameObject' => $nameObject,
            'identificationObject' => [
                'number' => $this->identification_number,
                'type' => $this->identification_type,
                'dv' => $this->dv,
            ],
            'name' => $this->business_name ?: $this->name,
            'kindOfPerson' => $this->kind_of_person,
            'regime' => $this->regime,
            'fiscalResponsabilities' => $this->fiscal_responsibilities && count($this->fiscal_responsibilities) > 0 && $this->fiscal_responsibilities[0] !== '0'
                ? array_map('strval', $this->fiscal_responsibilities)
                : null,
            'type' => 'client',
            'phonePrimary' => $this->phone,
            'email' => $this->email,
            'address' => [
                'address' => $this->address,
                'city' => $cityInfo['cityName'] ?? 'city',
                'department' => $cityInfo['departmentName'] ?? 'department',
                'zipCode' => $this->postcode
            ],
            'accounting' => [
                'debtToPay' => 6787,
                'accountReceivable' => 6551,
            ]
        ];
    }

    /**
     * Obtener información de la ciudad (placeholder - implementar según su BD)
     */
    private function getCityInfo($cityId): array
    {
        // TODO: Implementar consulta real a la BD de ciudades
        return [
            'cityName' => 'Bogotá',
            'departmentName' => 'Cundinamarca'
        ];
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

            // Para UPDATE: validar si estamos editando un cliente que ya tiene api_data_id
            if ($this->editingId) {
                $existingCustomer = Customer::find($this->editingId);
                if ($existingCustomer && $existingCustomer->api_data_id) {
                    Log::info('🔍 Validando actualización de cliente con API', [
                        'customer_id' => $existingCustomer->id,
                        'api_data_id' => $existingCustomer->api_data_id
                    ]);

                    try {
                        // Intentar actualizar en la API para validar datos
                        $validationResult = $apiClient->updateContact($existingCustomer->api_data_id, $apiData);

                        if (!$validationResult['success']) {
                            $errorMessage = $validationResult['message'] ?? 'Error desconocido en la API';
                            return [
                                'success' => false,
                                'message' => '⚠️ Error de Validación: ' . $errorMessage
                            ];
                        }

                        return [
                            'success' => true,
                            'message' => 'Validación de actualización exitosa'
                        ];

                    } catch (\Exception $e) {
                        return [
                            'success' => false,
                            'message' => '❌ Error de Conexión API: No se pudo validar la actualización. ' . $e->getMessage()
                        ];
                    }
                }
            }

            // Para CREATE: intentar crear en la API para validar y detectar duplicados
            Log::info('🔍 Validando creación de cliente con API', [
                'api_data' => $apiData
            ]);

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
                    if (strpos(strtolower($errorMessage), 'ya se encuentra') !== false ||
                        strpos(strtolower($errorMessage), 'duplicad') !== false ||
                        strpos(strtolower($errorMessage), 'existe') !== false) {

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
            $this->initializeCompanyConfiguration();

            $this->clearConfigurationCache();

            // TODO: Ajustar según la opción específica para clientes en su sistema
            $result = $this->isOptionEnabled(2); // Asumir opción 2 para clientes
            $value = $this->getOptionValue(2);

            // Contar clientes activos actuales con api_data_id
            $syncedClients = Customer::where('active', true)
                ->whereNotNull('api_data_id')
                ->count();

            Log::info('🔍 canSyncClients() verificación', [
                'companyId' => $this->currentCompanyId,
                'synced_clients_count' => $syncedClients,
                'plan_limit' => $value,
                'can_sync' => $syncedClients < $value
            ]);

            return $syncedClients < $value;

        } catch (\Exception $e) {
            Log::error('Error verificando límites de clientes', [
                'company_id' => $this->currentCompanyId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verificar si facturación electrónica está habilitada (similar a UserRapForm)
     */
    private function isElectronicInvoicingEnabled(?int $companyId): bool
    {
        try {
            if (!$companyId) {
                Log::warning('Company ID es null para verificación de facturación electrónica');
                return false;
            }

            if ($this->currentCompanyId !== $companyId) {
                $this->currentCompanyId = $companyId;
            }

            // Limpiar cache de DB para forzar nueva conexión
            DB::purge('tenant');

            // Consulta usando el servicio (cached)
            $cachedValue = $this->getOptionValue(8); // Opción 8 para facturación electrónica

            $enabled = $cachedValue !== null && $cachedValue > 0;

            Log::info('🔍 VALIDACIÓN FACTURACIÓN ELECTRÓNICA PARA CLIENTES', [
                'company_id' => $companyId,
                'option_value' => $cachedValue,
                'enabled_result' => $enabled,
            ]);

            return $enabled;

        } catch (\Exception $e) {
            Log::error('Error verificando facturación electrónica para cliente', [
                'company_id' => $companyId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Sincronizar cliente con API
     */
    private function syncCustomerWithApi(Customer $customer): array
    {
        try {
            Log::info('🔄 syncCustomerWithApi INICIO', ['customer_id' => $customer->id]);

            $authUser = Auth::user();

            $optimizedConfig = DatabaseConfigService::getFacturacionConfigByUser($authUser->id);
            if (!$optimizedConfig) {
                return [
                    'success' => false,
                    'message' => '⚙️ Error de Configuración: No se encontró configuración de facturación para el usuario.'
                ];
            }

            // Crear ApiClient
            $apiClient = new ApiClient(
                $optimizedConfig['base_url'],
                $optimizedConfig['token'],
                $optimizedConfig['username'],
                $optimizedConfig['timeout']
            );

            // Preparar datos para la API
            $apiData = $this->prepareApiDataFromCustomer($customer);

            // Sincronizar (crear o actualizar)
            if ($customer->api_data_id) {
                Log::info('📝 Actualizando cliente en API', [
                    'customer_id' => $customer->id,
                    'api_data_id' => $customer->api_data_id
                ]);
                $apiResult = $apiClient->updateContact($customer->api_data_id, $apiData);
            } else {
                Log::info('📝 Creando cliente en API', [
                    'customer_id' => $customer->id,
                    'name' => $customer->name
                ]);
                $apiResult = $apiClient->createContact($apiData);
            }

            if ($apiResult['success']) {
                // Guardar ID de la API si se creó exitosamente
                if (isset($apiResult['data']['id']) && !$customer->api_data_id) {
                    $customer->update(['api_data_id' => $apiResult['data']['id']]);
                }

                return [
                    'success' => true,
                    'message' => '✅ Sincronización Exitosa: El cliente ha sido sincronizado correctamente con la API de facturación electrónica.'
                ];
            } else {
                $errorMessage = $apiResult['message'] ?? 'Error desconocido en la API';
                return [
                    'success' => false,
                    'message' => '🌐 Error de Comunicación API: No se pudo sincronizar el cliente con la API de facturación. Detalle técnico: ' . $errorMessage
                ];
            }

        } catch (\Exception $e) {
            Log::error('❌ Excepción sincronizando cliente', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => '❌ Error Interno del Sistema: Ocurrió un problema inesperado durante la sincronización del cliente. Por favor intente nuevamente o contacte al soporte técnico. Detalle: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Preparar datos para API desde el modelo Customer
     */
    private function prepareApiDataFromCustomer(Customer $customer): array
    {
        $nameObject = null;
        if ($customer->kind_of_person !== 'LEGAL_ENTITY') {
            $nameObject = [
                'firstName' => $customer->name,
                'lastName' => $customer->name
            ];
        }

        $cityInfo = $this->getCityInfo($customer->city_id);

        return [
            'nameObject' => $nameObject,
            'identificationObject' => [
                'number' => $customer->identification_number,
                'type' => $customer->identification_type,
                'dv' => $customer->dv,
            ],
            'name' => $customer->business_name ?: $customer->name,
            'kindOfPerson' => $customer->kind_of_person,
            'regime' => $customer->regime,
            'fiscalResponsabilities' => $customer->fiscal_responsibilities && count($customer->fiscal_responsibilities) > 0
                ? array_map('strval', $customer->fiscal_responsibilities)
                : null,
            'type' => 'client',
            'phonePrimary' => $customer->phone,
            'email' => $customer->email,
            'address' => [
                'address' => $customer->address,
                'city' => $cityInfo['cityName'] ?? 'city',
                'department' => $cityInfo['departmentName'] ?? 'department',
                'zipCode' => $customer->postcode
            ],
            'accounting' => [
                'debtToPay' => 6787,
                'accountReceivable' => 6551,
            ]
        ];
    }

    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');

        Log::info('🔄 INICIANDO ensureTenantConnection', [
            'session_tenant_id' => $tenantId,
            'method' => 'CustomerForm::ensureTenantConnection'
        ]);

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            throw new \Exception('Invalid tenant');
        }

        Log::info('🏢 TENANT ENCONTRADO', [
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'tenant_db_name' => $tenant->db_name,
            'method' => 'CustomerForm::ensureTenantConnection'
        ]);

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Log después de establecer conexión
        try {
            $connectionConfig = config('database.connections.tenant');
            $actualDatabaseName = DB::connection('tenant')->getDatabaseName();

            Log::info('🔗 CONEXIÓN TENANT ESTABLECIDA EN CUSTOMERFORM', [
                'config_database' => $connectionConfig['database'] ?? 'NULL',
                'actual_database_name' => $actualDatabaseName,
                'tenant_id' => $tenant->id,
                'method' => 'CustomerForm::ensureTenantConnection'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ ERROR AL VERIFICAR CONEXIÓN EN CUSTOMERFORM', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'method' => 'CustomerForm::ensureTenantConnection'
            ]);
        }

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
