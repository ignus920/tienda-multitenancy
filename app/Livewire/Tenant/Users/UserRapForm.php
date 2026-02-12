<?php

namespace App\Livewire\Tenant\Users;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Auth\User;
use App\Models\Auth\UserTenant;
use App\Models\Central\UsrProfile;
use App\Models\Central\UsrPermissionProfile;
use App\Models\Central\VntWarehouse;
use App\Models\Central\VntContact;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\UserExportService;
use App\Traits\HasCompanyConfiguration;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

// sincronizacion con el api
use App\Services\Facturacion\DatabaseConfigService;
use App\Services\Facturacion\ApiClient;
use App\Models\Tenant\CnfInvoice;




class UserRapForm extends Component
{
    use WithPagination, HasCompanyConfiguration;
    
    protected $listeners = ['positionUpdated', 'refresh-users' => 'refreshUsers'];
    
    protected UserExportService $exportService;
    // Table properties
    public $search = '';
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    // Modal control properties
    public $showModal = false;
    public $editingId = null;

    // Form properties

    // Propiedades para contactos 
    public $firstName = '';
    public $secondName = '';
    public $lastName = '';
    public $secondLastName = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $phone = '';
    public $positionId = null;
    public $status = 1;
    public $profile_id = null;




    public $warehouseId = null;
    public $avatar = null;
    public $two_factor_enabled = false;
    public $two_factor_type = null;

    // Data collection properties
    public $profiles = []; // Lista de perfiles disponibles para asignar al usuario
    public $warehouses = []; // Lista de sucursales disponibles según la compañía
    public $profilePermissions = []; // Permisos del perfil seleccionado para mostrar al usuario

    // Message properties
    public $successMessage = '';
    public $errorMessage = '';

    // Change Password Modal properties
    public $showChangePasswordModal = false;
    public $userToChangePassword = null;
    public $newPassword = '';
    public $confirmPassword = '';

    /**
     * Boot method to inject dependencies
     */
    public function boot(UserExportService $exportService): void
    {
        $this->exportService = $exportService;
    }

    /**
     * Mount component and load initial data
     */
    public function mount(): void
    {

        $this->loadProfiles();
        $this->loadWarehouses();
    }

    /**
     * Handle position update from PositionSelect component
     */
    public function positionUpdated($positionId): void
    {
        $this->positionId = $positionId;
    }

    /**
     * Refresh users list after status toggle
     */
    public function refreshUsers(): void
    {
        // This method is called via dispatch to force re-render
        // Livewire will automatically re-query the users property
    }

    /**
     * Load profiles from database
     */
    private function loadProfiles(): void
    {
        $centralDbName = config('database.connections.central.database');
        $this->profiles = UsrProfile::where('status', 1)
            ->whereExists(function($query) use ($centralDbName) {
               $query->select(DB::raw(1))
                   ->from("{$centralDbName}.usr_profile_merchant as upm")
                   ->whereColumn('upm.profile_id', 'usr_profiles.id')
                   ->where('upm.merchant_type_id', 4);
           })
            ->orderBy('name')
            ->get();
    }

    /**
     * Load warehouses with company relationship
     */
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


       
    /**
     * Cargar permisos del perfil seleccionado
     * Obtiene todos los permisos asociados al perfil con sus respectivos niveles de acceso
     */
    public function loadProfilePermissions($profileId = null): void
    {
        if (!$profileId) {
            $this->profilePermissions = [];
            return;
        }

        try {
            $profile = UsrProfile::with(['permissions' => function($query) {
                $query->active();
            }])->find($profileId);

            if (!$profile) {
                $this->profilePermissions = [];
                return;
            }

            $this->profilePermissions = $profile->permissions->map(function($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'ver' => (bool)($permission->pivot->show ?? false),
                    'crear' => (bool)($permission->pivot->creater ?? false),
                    'editar' => (bool)($permission->pivot->editer ?? false),
                    'eliminar' => (bool)($permission->pivot->deleter ?? false),
                ];
            })->toArray();

        } catch (\Exception $e) {
            Log::error('Error loading profile permissions', [
                'profile_id' => $profileId,
                'error' => $e->getMessage()
            ]);
            $this->profilePermissions = [];
        }
    }

    /**
     * Manejar el cambio de selección de perfil
     * Se ejecuta automáticamente cuando el usuario selecciona un perfil
     */
    public function updatedProfileId($profileId): void
    {
        $this->loadProfilePermissions($profileId);
    }


    

    /**
     * Open modal in create mode
     */
    public function create(): void
    {
        if (!$this->canCreateUser()) {
            $limit = $this->getUserLimit();
            $current = $this->getActiveUserCount();
            $this->errorMessage = "No se pueden crear más usuarios. Límite: {$limit}, Activos: {$current}";
            return;
        }

        // Limpiar completamente el estado antes de abrir
        $this->resetForm();
        $this->resetErrorBag();
        $this->resetValidation();
        
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->editingId = null;
        
        $this->showModal = true;
    }

    /**
     * Open modal in edit mode with user data
     */
    public function edit(int $userId): void
    {
        // Limpiar completamente el estado anterior
        $this->resetForm();
        $this->resetErrorBag();
        $this->resetValidation();
        $this->successMessage = '';
        $this->errorMessage = '';
        
        $user = User::with('contact')->findOrFail($userId);
        
        $this->editingId = $userId;
        
        // Load user data
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->profile_id = $user->profile_id;
        $this->two_factor_enabled = $user->two_factor_enabled ?? false;
        $this->two_factor_type = $user->two_factor_type;

        // Load contact data if exists
        if ($user->contact) {
            $this->firstName = $user->contact->firstName;
            $this->secondName = $user->contact->secondName;
            $this->lastName = $user->contact->lastName;
            $this->secondLastName = $user->contact->secondLastName;
            $this->warehouseId = $user->contact->warehouseId;
            $this->positionId = $user->contact->positionId;
        }

        // Cargar permisos del perfil si existe
        if ($this->profile_id) {
            $this->loadProfilePermissions($this->profile_id);
        }

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
        
        // Limpiar validaciones completamente
        $this->resetErrorBag();
        $this->resetValidation();
    }

    /**
     * Reset all form fields
     */
    private function resetForm(): void
    {
        $this->editingId = null;
        $this->firstName = '';
        $this->secondName = '';
        $this->lastName = '';
        $this->secondLastName = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->phone = '';
        $this->profile_id = null;
        $this->warehouseId = null;
        $this->positionId = null;
        $this->avatar = null;
        $this->two_factor_enabled = false;
        $this->two_factor_type = null;
        $this->profilePermissions = []; // Limpiar permisos cuando se resetea el formulario
    }

    /**
     * Validation rules for form fields
     */
    protected function rules(): array
    {
        $rules = [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'secondName' => 'nullable|string|max:255',
            'secondLastName' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingId)
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^3[0-9]{9}$/',
                'digits:10'
            ],
            'profile_id' => 'required|exists:usr_profiles,id',
            'warehouseId' => 'required|exists:vnt_warehouses,id',
            'positionId' => 'required|exists:cnf_positions,id',
            'avatar' => 'nullable|image|max:2048',
            'two_factor_enabled' => 'boolean',
            'two_factor_type' => 'nullable|in:email,sms,authenticator',
        ];

        // Password validation only for create mode
        if (!$this->editingId) {
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['password_confirmation'] = 'required';
        }

        return $rules;
    }

    /**
     * Custom validation messages in Spanish
     */
    protected function messages(): array
    {
        return [
            'firstName.required' => 'El primer nombre es obligatorio',
            'firstName.string' => 'El primer nombre debe ser texto',
            'firstName.max' => 'El primer nombre no debe superar 255 caracteres',
            'lastName.required' => 'El primer apellido es obligatorio',
            'lastName.string' => 'El primer apellido debe ser texto',
            'lastName.max' => 'El primer apellido no debe superar 255 caracteres',
            'secondName.string' => 'El segundo nombre debe ser texto',
            'secondName.max' => 'El segundo nombre no debe superar 255 caracteres',
            'secondLastName.string' => 'El segundo apellido debe ser texto',
            'secondLastName.max' => 'El segundo apellido no debe superar 255 caracteres',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'El email debe ser válido',
            'email.max' => 'El email no debe superar 255 caracteres',
            'email.unique' => 'Este email ya está registrado',
            'phone.regex' => 'El número debe ser un celular colombiano válido que inicie con 3 (ej: 3123456789).',
            'phone.digits' => 'El número de celular debe tener exactamente 10 dígitos.',
            'phone.required' => 'El número de celular es obligatorio',
            'phone.max' => 'El teléfono no debe superar 20 caracteres',
            'password.required' => 'La contraseña es obligatoria',
            'password.string' => 'La contraseña debe ser texto',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'password_confirmation.required' => 'La confirmación de contraseña es obligatoria',
            'profile_id.required' => 'Debe seleccionar un perfil',
            'profile_id.exists' => 'El perfil seleccionado no es válido',
            'warehouseId.required' => 'Debe seleccionar una sucursal',
            'warehouseId.exists' => 'La sucursal seleccionada no es válida',
            'positionId.required' => 'Debe seleccionar un cargo',
            'positionId.exists' => 'El cargo seleccionado no es válido',
            // 'avatar.image' => 'El archivo debe ser una imagen',
            // 'avatar.max' => 'La imagen no debe superar 2MB',
            // 'two_factor_enabled.boolean' => 'El valor de autenticación de dos factores debe ser verdadero o falso',
            // 'two_factor_type.in' => 'El tipo de autenticación de dos factores debe ser email, sms o authenticator',
        ];
    }

    /**
     * Validate form fields
     */
    private function validateForm(): void
    {
        $this->validate($this->rules(), $this->messages());
    }

    /**
     * Save user (create or update)
     */
    public function save(): void
    {
        try {
            // Clear previous error message
            $this->errorMessage = '';
            $this->successMessage = '';

            // CRITICAL: Ensure tenant connection is configured BEFORE any database queries
            $this->ensureTenantConnection();

            // Validate all inputs
            $this->validateForm();

            // PASO 1: Validar que es posible sincronizar con la API ANTES de guardar
            $preValidationResult = $this->preValidateApiSync();
            if (!$preValidationResult['success']) {
                // Cerrar modal y mostrar error con session flash para mayor visibilidad
                $this->closeModal();
                session()->flash('sync_error', $preValidationResult['message']);
                return;
            }

            // PASO 2: Preparar datos de API ANTES de crear usuario para validar duplicados
            $tempApiData = [
                'name' => $this->concatenateFullName(),
                'identification' => $this->phone, // Usar teléfono como identificación
                'observations' => 'vendedor',
                'status' => 'active'
            ];

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

            $user = null;

            // PASO 4: Guardar en base de datos local solo si API validó correctamente
            if ($this->editingId) {
                // Update mode
                $user = User::findOrFail($this->editingId);
                $this->updateUserWithContact($user);
            } else {
                // Create mode
                $user = $this->createUserWithContact();
            }

            // PASO 5: Finalizar sincronización con API
            if ($user) {
                try {
                    set_time_limit(45); // Aumentar a 45 segundos para sincronización API

                    // Si ya tenemos temp_api_id, solo necesitamos asignarlo al usuario
                    if ($tempApiId && !$this->editingId) {
                        Log::info('✅ Usando API ID temporal de validación', [
                            'user_id' => $user->id,
                            'temp_api_id' => $tempApiId
                        ]);

                        $user->update(['api_data_id' => $tempApiId]);
                        $syncResult = [
                            'success' => true,
                            'message' => 'Vendedor sincronizado usando validación previa'
                        ];
                    } else {
                        // Sincronización normal (para updates o cuando no hay temp_api_id)
                        $syncResult = $this->syncUserWithApi($user);
                    }

                    set_time_limit(60); // Restaurar timeout normal

                    if ($syncResult['success']) {
                        session()->flash('sync_message', '✅ Vendedor Sincronizado: El usuario ha sido creado/actualizado exitosamente y sincronizado con la API de facturación electrónica.');
                    } else {
                        // Si falla sincronización después de validar, eliminar usuario creado
                        if (!$this->editingId) {
                            $user->delete();
                        }
                        $this->closeModal();
                        session()->flash('sync_error', '❌ Error de Sincronización API: ' . $syncResult['message']);
                        return;
                    }
                } catch (\Exception $e) {
                    set_time_limit(60); // Restaurar timeout normal
                    // Si falla sincronización después de validar, eliminar usuario creado
                    if (!$this->editingId) {
                        $user->delete();
                    }

                    Log::error('Error en sincronización post-validación', [
                        'user_id' => $user->id,
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
            throw $e;
        } catch (\Exception $e) {
            $this->errorMessage = 'Error inesperado: ' . $e->getMessage();
            Log::error('Unexpected error in save method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Concatenate full name from name fields
     */
    private function concatenateFullName(): string
    {
        $nameParts = array_filter([
            $this->firstName,
            $this->secondName,
            $this->lastName,
            $this->secondLastName
        ]);
        
        return implode(' ', $nameParts);
    }

    /**
     * Create user with associated contact record
     * @return User|null
     */
    private function createUserWithContact(): ?User
    {
        try {
            $sessionTenant = $this->getTenantId();
            DB::beginTransaction();

            // Create VntContact record
            $contact = VntContact::create([
                'firstName' => $this->firstName,
                'secondName' => $this->secondName,
                'lastName' => $this->lastName,
                'secondLastName' => $this->secondLastName,
                'email' => $this->email,
                'phone_contact' => $this->phone,
                'warehouseId' => $this->warehouseId,
                'positionId' => $this->positionId,
                'status' => true,
            ]);

            // Concatenate full name for User.name
            $fullName = $this->concatenateFullName();

            // Create User record
            $user = User::create([
                'name' => $fullName,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'phone' => $this->phone,
                'profile_id' => $this->profile_id,
                'contact_id' => $contact->id,
                'avatar' => $this->avatar,
                'two_factor_failed_attempts' => 0,
                'two_factor_locked_until' => null,
            ]);

            // Asociar el usuario con el tenant
            UserTenant::create([
                'user_id' => $user->id,
                'tenant_id' => $sessionTenant,
                'role' => $this->profile_id,
                'is_active' => 1
            ]);

            DB::commit();
            
            // Set success message before closing
            $this->successMessage = 'Usuario creado exitosamente';
            $this->errorMessage = '';
            
            // Close modal after successful creation
            $this->closeModal();

            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Error al crear el usuario: ' . $e->getMessage();
            
            Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'email' => $this->email,
                    'profile_id' => $this->profile_id,
                    'warehouseId' => $this->warehouseId,
                ]
            ]);

            return null;
        }
    }

    /**
     * Update user with associated contact record
     * @return User
     */
    private function updateUserWithContact(User $user): User
    {
        try {
            DB::beginTransaction();

            // Load existing User and VntContact records
            $contact = VntContact::findOrFail($user->contact_id);

            // Update VntContact fields
            $contact->update([
                'firstName' => $this->firstName,
                'secondName' => $this->secondName,
                'lastName' => $this->lastName,
                'secondLastName' => $this->secondLastName,
                'email' => $this->email,
                'phone_contact' => $this->phone,
                'warehouseId' => $this->warehouseId,
                'positionId' => $this->positionId,
            ]);

            // Concatenate full name from updated name fields
            $fullName = $this->concatenateFullName();

            // Update User fields (excluding password)
            $user->update([
                'name' => $fullName,
                'phone' => $this->phone,
                'profile_id' => $this->profile_id,
                'avatar' => $this->avatar,
                'two_factor_enabled' => $this->two_factor_enabled,
                'two_factor_type' => $this->two_factor_type,
            ]);

            DB::commit();
            
            // Set success message before closing
            $this->successMessage = 'Usuario actualizado exitosamente';
            $this->errorMessage = '';
            
            // Close modal after successful update
            $this->closeModal();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Error al actualizar el usuario: ' . $e->getMessage();
            
            Log::error('User update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'user_id' => $user->id,
                    'email' => $this->email,
                    'profile_id' => $this->profile_id,
                    'warehouseId' => $this->warehouseId,
                ]
            ]);
        }
    }

    /**
     * Computed property to get users with relationships
     */
    public function getUsersProperty()
    {
        $sessionTenant = $this->getTenantId();

        return User::query()
            ->whereHas('tenants', function ($query) use ($sessionTenant) {
                $query->where('tenants.id', $sessionTenant);
            })
            ->with(['profile', 'contact.warehouse.company'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    /**
     * Reset pagination when search is updated
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle sort field and direction
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            // Toggle direction if same field
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new field and default to ascending
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Toggle user status in vnt_contacts and user_tenants
     * 
     * @param int $userId ID del usuario a actualizar
     * @return void
     */
    public function toggleItemStatus(int $userId): void
    {
        try {
            // 1. Obtener tenant actual
            $tenantId = $this->getTenantId();
            
            // 2. Buscar usuario con relaciones
            $user = User::with('contact')->findOrFail($userId);
            
            // 3. Validar que tiene contacto
            if (!$user->contact) {
                throw new \Exception('Usuario sin contacto asociado');
            }
            
            // 4. Buscar relación UserTenant
            $userTenant = UserTenant::where('user_id', $userId)
                ->where('tenant_id', $tenantId)
                ->firstOrFail();
            
            // 5. Iniciar transacción
            DB::beginTransaction();
            
            // 6. Calcular nuevo estado (toggle)
            $newStatus = !$user->contact->status;
            
            // 7. Validar según la operación
            if ($newStatus) {
                // Activating user - check if allowed
                if (!$this->canActivateUser()) {
                    DB::rollBack();
                    $limit = $this->getUserLimit();
                    $current = $this->getActiveUserCount();
                    $this->errorMessage = "No se pueden activar más usuarios. Límite: {$limit}, Activos: {$current}";
                    return;
                }
            } else {
                // Deactivating user - always allowed but check anyway for consistency
                if (!$this->canDeactivateUser()) {
                    DB::rollBack();
                    $this->errorMessage = 'Error al desactivar usuario';
                    return;
                }
            }
            
            // 8. Actualizar vnt_contacts
            $user->contact->update(['status' => $newStatus]);
            
            // 9. Actualizar user_tenants
            $userTenant->update(['is_active' => $newStatus]);
            
            // 10. Confirmar transacción
            DB::commit();
            
            // 11. Mensaje de éxito
            $this->successMessage = 'Estado actualizado exitosamente';
            
            // Clear any error messages
            $this->errorMessage = '';
            
            // 12. Forzar re-render de la tabla para actualizar el switch
            $this->dispatch('refresh-users');
            
        } catch (\Exception $e) {
            // Rollback y manejo de error
            DB::rollBack();
            $this->errorMessage = 'Error al actualizar el estado: ' . $e->getMessage();
            
            Log::error('Toggle status failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Delete user
     */
    public function delete(int $userId): void
    {
        try {
            // Check to prevent deletion of currently logged-in user
            if (Auth::id() === $userId) {
                $this->errorMessage = 'No puedes eliminar tu propio usuario';
                return;
            }

            // Find and delete the user
            $user = User::findOrFail($userId);
            $user->delete();

            // Set success message
            $this->successMessage = 'Usuario eliminado exitosamente';
            
            // Clear any error messages
            $this->errorMessage = '';
            
        } catch (\Exception $e) {
            // Set error message on failure
            $this->errorMessage = 'Error al eliminar el usuario: ' . $e->getMessage();
            
            Log::error('User deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
            ]);
        }
    }

    /**
     * Open change password modal for a specific user
     */
    /**
     * Open the change password modal
     */
    public function openChangePasswordModal(int $userId): void
    {
        try {
            $this->userToChangePassword = User::findOrFail($userId);
            $this->resetChangePasswordForm();
            $this->showChangePasswordModal = true;
        } catch (\Exception $e) {
            $this->errorMessage = 'Error: Usuario no encontrado';
        }
    }

    /**
     * Close the change password modal
     */
    public function closeChangePasswordModal(): void
    {
        $this->showChangePasswordModal = false;
        $this->resetChangePasswordForm();
    }

    /**
     * Reset the change password form
     */
    public function resetChangePasswordForm(): void
    {
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->resetValidation(['newPassword', 'confirmPassword']);
    }

    /**
     * Change user password
     */
    public function changePassword(): void
    {
        $this->validate([
            'newPassword' => 'required|min:8',
            'confirmPassword' => 'required|same:newPassword',
        ], [
            'newPassword.required' => 'La nueva contraseña es requerida.',
            'newPassword.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'confirmPassword.required' => 'La confirmación de contraseña es requerida.',
            'confirmPassword.same' => 'Las contraseñas no coinciden.',
        ]);

        try {
            $this->userToChangePassword->update([
                'password' => Hash::make($this->newPassword)
            ]);

            $this->successMessage = 'Contraseña actualizada exitosamente para ' . $this->userToChangePassword->name;
            $this->closeChangePasswordModal();

            // Clear messages after a delay
            $this->dispatch('$nextTick', fn() =>
                $this->dispatch('$nextTick', fn() =>
                    $this->dispatch('clearMessages')
                )
            );

        } catch (\Exception $e) {
            $this->errorMessage = 'Error al cambiar la contraseña: ' . $e->getMessage();
        }
    }

    /**
     * Export users to Excel format
     */
    public function exportExcel()
    {
        try {
            $result = $this->exportService->exportToExcel($this->search);
            
            if (!$result['success']) {
                $this->errorMessage = $result['message'];
                return;
            }
            
            return $result['download'];
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al exportar: ' . $e->getMessage();
            Log::error('Export Excel failed in component', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Export users to PDF format
     */
    public function exportPdf()
    {
        try {
            $result = $this->exportService->exportToPdf($this->search);
            
            if (!$result['success']) {
                $this->errorMessage = $result['message'];
                return;
            }
            
            return $result['download'];
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al exportar: ' . $e->getMessage();
            Log::error('Export PDF failed in component', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Export users to CSV format
     */
    public function exportCsv()
    {
        try {
            $result = $this->exportService->exportToCsv($this->search);
            
            if (!$result['success']) {
                $this->errorMessage = $result['message'];
                return;
            }
            
            return $result['download'];
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al exportar: ' . $e->getMessage();
            Log::error('Export CSV failed in component', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.tenant.users.components.user-rap-form', [
            'users' => $this->users,
        ]);
    }

    private function getTenantId()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }
        return $tenantId;
    }

    /**
     * Get the count of currently active users for the tenant
     * Active = user_tenants.is_active = 1 AND vnt_contacts.status = 1
     * 
     * @return int
     */
    private function getActiveUserCount(): int
    {
        try {
            $sessionTenant = $this->getTenantId();

            $count = User::query()
                ->whereHas('tenants', function ($query) use ($sessionTenant) {
                    $query->where('tenants.id', $sessionTenant)
                          ->where('user_tenants.is_active', 1);
                })
                ->whereHas('contact', function ($query) {
                    $query->where('status', 1);
                })
                ->count();

            Log::info('Active user count retrieved', [
                'tenant_id' => $sessionTenant,
                'count' => $count
            ]);

            return $count;
        } catch (\Exception $e) {
            Log::error('Failed to get active user count', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Fail closed - return high value to block operations
            return PHP_INT_MAX;
        }
    }

    /**
     * Get the user limit for the current tenant from configuration
     * Retrieves option_id = 1 from cnf_company_options
     * 
     * @return int
     */
    private function getUserLimit(): int
    {
        try {
            $this->ensureTenantConnection();
            $this->initializeCompanyConfiguration();
            $this->clearConfigurationCache();

            $limit = $this->getOptionValue(1);

            // Validate limit is a positive integer
            if (!is_numeric($limit) || $limit < 0) {
                Log::warning('Invalid user limit configuration', [
                    'limit_value' => $limit,
                    'company_id' => $this->currentCompanyId
                ]);
                return 0;
            }

            Log::info('User limit retrieved', [
                'company_id' => $this->currentCompanyId,
                'limit' => $limit
            ]);

            return (int)$limit;
        } catch (\Exception $e) {
            Log::error('Failed to get user limit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Fail closed - return 0 to block operations
            return 0;
        }
    }

    /**
     * Check if current active users are below the limit
     * Returns true if there is capacity for more users
     * 
     * @return bool
     */
    private function hasAvailableUserSlots(): bool
    {
        $activeCount = $this->getActiveUserCount();
        $limit = $this->getUserLimit();

        $hasSlots = $activeCount < $limit;

        Log::info('Checking available user slots', [
            'active_count' => $activeCount,
            'limit' => $limit,
            'has_slots' => $hasSlots
        ]);

        return $hasSlots;
    }

    /**
     * Check if a new user can be created
     * Returns true if creation is allowed, false otherwise
     * 
     * @return bool
     */
    private function canCreateUser(): bool
    {
        $canCreate = $this->hasAvailableUserSlots();

        Log::info('User creation validation', [
            'can_create' => $canCreate,
            'active_count' => $this->getActiveUserCount(),
            'limit' => $this->getUserLimit()
        ]);

        return $canCreate;
    }

    /**
     * Check if an inactive user can be activated
     * Returns true if activation is allowed, false otherwise
     * 
     * @return bool
     */
    private function canActivateUser(): bool
    {
        $canActivate = $this->hasAvailableUserSlots();

        Log::info('User activation validation', [
            'can_activate' => $canActivate,
            'active_count' => $this->getActiveUserCount(),
            'limit' => $this->getUserLimit()
        ]);

        return $canActivate;
    }

    /**
     * Check if an active user can be deactivated
     * Always returns true (deactivation is always allowed)
     * 
     * @return bool
     */
    private function canDeactivateUser(): bool
    {
        Log::info('User deactivation validation - always allowed');
        return true;
    }

    /**
     * Verificar si el plan permite sincronizar vendedores
     * Similar a canCreateOrUpdateUsers pero para vendedores
     */
    private function canSyncVendors(): bool
    {
        try {
            $this->ensureTenantConnection();
            $this->initializeCompanyConfiguration();

            // Limpiar caché para testing
            $this->clearConfigurationCache();

            // Verificar límite de vendedores (podría usar el mismo option_id que usuarios o uno específico)
            $result = $this->isOptionEnabled(1); // Usar mismo que usuarios por ahora
            $value = $this->getOptionValue(1);

            // Contar vendedores activos actuales con api_data_id (ya sincronizados)
            $syncedVendors = $this->users->filter(function($user) {
                return $user->contact &&
                       $user->contact->status == 1 &&
                       !empty($user->api_data_id); // Solo contar los que ya están sincronizados
            });
            $syncedCount = $syncedVendors->count();

            Log::info('🔍 canSyncVendors() verificación', [
                'companyId' => $this->currentCompanyId,
                'option_id' => 1,
                'result' => $result ? 'TRUE' : 'FALSE',
                'option_value' => $value,
                'synced_vendors_count' => $syncedCount,
                'plan_limit' => $value,
                'can_sync' => $syncedCount < $value
            ]);

            // Permitir sincronización si el número de vendedores sincronizados es menor que el límite
            return $syncedCount < $value;

        } catch (\Exception $e) {
            Log::error('Error verificando límites de vendedores', [
                'company_id' => $this->currentCompanyId,
                'error' => $e->getMessage()
            ]);
            return false;
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
                        'message' => '⚙️ Error de Configuración: No se pudo obtener la configuración de la empresa para sincronizar vendedores. Verifique que el usuario esté correctamente asignado a una empresa o contacte al administrador del sistema.'
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
                    'message' => '⚠️ Facturación Electrónica Deshabilitada: Para sincronizar vendedores con la API de facturación, debe activar el módulo de facturación electrónica en la configuración de la empresa.'
                ];
            }

            // Verificar límites del plan para vendedores
            if (!$this->canSyncVendors()) {
                return [
                    'success' => false,
                    'message' => '📊 Límite del Plan Alcanzado: Ha alcanzado el máximo número de vendedores permitidos en su plan actual. Para sincronizar más vendedores, considere actualizar su plan o contacte al administrador.'
                ];
            }

            // Verificar autenticación
            $authUser = Auth::user();
            if (!$authUser) {
                return [
                    'success' => false,
                    'message' => '🔐 Error de Autenticación: Su sesión ha expirado. Por favor inicie sesión nuevamente para sincronizar vendedores con la API de facturación.'
                ];
            }

            // Verificar configuración de API
            $this->ensureTenantConnection();
            $optimizedConfig = DatabaseConfigService::getFacturacionConfigByUser($authUser->id);
            if (!$optimizedConfig) {
                return [
                    'success' => false,
                    'message' => '⚙️ Error de Configuración API: No se encontró la configuración de facturación electrónica para sincronizar vendedores. Verifique que la configuración esté correctamente establecida en el módulo de facturación o contacte al administrador.'
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
     * Validar datos con la API antes de crear el usuario localmente
     * Hace una validación real con la API para detectar duplicados y errores
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

            // Para UPDATE: validar si estamos editando un usuario que ya tiene api_data_id
            if ($this->editingId) {
                $existingUser = User::find($this->editingId);
                if ($existingUser && $existingUser->api_data_id) {
                    // Para UPDATE hacer una validación de actualización con la API
                    Log::info('🔍 Validando actualización con API', [
                        'user_id' => $existingUser->id,
                        'api_data_id' => $existingUser->api_data_id,
                        'api_data' => $apiData
                    ]);

                    try {
                        // Intentar actualizar en la API para validar datos
                        $validationResult = $apiClient->updateSeller($existingUser->api_data_id, $apiData);

                        if (!$validationResult['success']) {
                            $errorMessage = $validationResult['message'] ?? 'Error desconocido en la API';
                            Log::warning('❌ Validación de actualización falló', [
                                'api_data_id' => $existingUser->api_data_id,
                                'error' => $errorMessage
                            ]);

                            return [
                                'success' => false,
                                'message' => '⚠️ Error de Validación: ' . $errorMessage
                            ];
                        }

                        Log::info('✅ Validación de actualización exitosa', [
                            'api_data_id' => $existingUser->api_data_id
                        ]);

                        return [
                            'success' => true,
                            'message' => 'Validación de actualización exitosa'
                        ];

                    } catch (\Exception $e) {
                        Log::error('❌ Error en validación de actualización', [
                            'api_data_id' => $existingUser->api_data_id,
                            'error' => $e->getMessage()
                        ]);

                        return [
                            'success' => false,
                            'message' => '❌ Error de Conexión API: No se pudo validar la actualización. ' . $e->getMessage()
                        ];
                    }
                } else {
                    // Usuario sin api_data_id, tratar como creación nueva
                    Log::info('Usuario existente sin api_data_id, tratando como creación nueva');
                }
            }

            // Para CREATE: intentar crear en la API para validar y detectar duplicados
            Log::info('🔍 Validando creación con API (intento real)', [
                'api_data' => $apiData,
                'validation_type' => 'real_api_creation_test'
            ]);

            try {
                // Hacer un intento REAL de creación para detectar errores de validación
                set_time_limit(30); // Timeout más corto para validación
                $validationResult = $apiClient->createSeller($apiData);
                set_time_limit(60); // Restaurar timeout

                if (!$validationResult['success']) {
                    $errorMessage = $validationResult['message'] ?? 'Error desconocido en la API';

                    Log::warning('❌ API rechazó la creación durante validación', [
                        'api_data' => $apiData,
                        'error' => $errorMessage,
                        'preventing_local_save' => true
                    ]);

                    // Detectar diferentes tipos de error y dar mensajes específicos
                    if (strpos(strtolower($errorMessage), 'ya se encuentra') !== false ||
                        strpos(strtolower($errorMessage), 'duplicad') !== false ||
                        strpos(strtolower($errorMessage), 'existe') !== false) {

                        return [
                            'success' => false,
                            'message' => '🔄 Vendedor Duplicado: ' . $errorMessage . ' Por favor use un nombre o identificación diferente.'
                        ];
                    }

                    return [
                        'success' => false,
                        'message' => '⚠️ Error de Validación API: ' . $errorMessage
                    ];
                }

                // Si la API aceptó la creación, necesitamos eliminar el registro que se creó
                // ya que esto es solo validación
                if (isset($validationResult['data']['id'])) {
                    $tempApiId = $validationResult['data']['id'];
                    Log::info('✅ Validación exitosa, eliminando registro temporal de API', [
                        'temp_api_id' => $tempApiId,
                        'api_data' => $apiData
                    ]);

                    // Eliminar el registro temporal que se creó para validación
                    try {
                        // Nota: Necesitarías implementar un método deleteSeller en ApiClient
                        // Por ahora, guardaremos el ID para usar en la creación real
                        return [
                            'success' => true,
                            'message' => 'Datos válidos para sincronización',
                            'temp_api_id' => $tempApiId // Guardar para reutilizar
                        ];
                    } catch (\Exception $e) {
                        Log::warning('No se pudo eliminar registro temporal de validación', [
                            'temp_api_id' => $tempApiId,
                            'error' => $e->getMessage()
                        ]);
                        // Continuar - el registro será sobrescrito en la sincronización real
                        return [
                            'success' => true,
                            'message' => 'Datos válidos para sincronización',
                            'temp_api_id' => $tempApiId
                        ];
                    }
                } else {
                    Log::info('✅ API aceptó datos sin devolver ID');
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

    private function syncUserWithApi(User $user): array
    {
        try {
            Log::info('🔄 syncUserWithApi INICIO', ['user_id' => $user->id]);

            // Verificar que tenemos company_id válido
            if (!$this->currentCompanyId) {
                Log::warning('⚠️ currentCompanyId es NULL - inicializando configuración');
                $this->initializeCompanyConfiguration();

                if (!$this->currentCompanyId) {
                    Log::error('❌ No se pudo obtener currentCompanyId después de inicialización', [
                        'user_id' => $user->id,
                        'currentCompanyId_after_init' => $this->currentCompanyId
                    ]);
                    return [
                        'success' => false,
                        'message' => '⚙️ Error de Configuración: No se pudo obtener la configuración de la empresa para sincronizar vendedores. Verifique que el usuario esté correctamente asignado a una empresa o contacte al administrador del sistema.'
                    ];
                }
            }

            Log::info('✅ currentCompanyId confirmado', [
                'user_id' => $user->id,
                'currentCompanyId' => $this->currentCompanyId,
                'is_valid' => !empty($this->currentCompanyId)
            ]);

            Log::info('🏢 COMPANY ID DETECTADO PARA VALIDACIÓN', [
                'user_id' => $user->id,
                'company_id_used' => $this->currentCompanyId,
                'expected_companies_in_db' => 'Company 1 (value=10), Company 7 (value=0), Company 8 (value=0)'
            ]);

            // LIMPIAR CACHE ANTES DE VALIDACIÓN (para asegurar datos frescos)
            $this->clearConfigurationCache();
            Log::info('🧹 Cache limpiado antes de validación de facturación electrónica');

            // VERIFICACIÓN DIRECTA EN BD (sin cache)
            try {
                $directDbCheck = DB::connection('tenant')->table('cnf_company_options')
                    ->where('company_id', $this->currentCompanyId)
                    ->where('option_id', 8)
                    ->whereNull('deleted_at')
                    ->first();

                Log::info('🔍 VERIFICACIÓN DIRECTA EN BD (SIN CACHE)', [
                    'company_id' => $this->currentCompanyId,
                    'query_result' => $directDbCheck ? [
                        'id' => $directDbCheck->id,
                        'value' => $directDbCheck->value,
                        'raw_value' => $directDbCheck->value,
                        'value_type' => gettype($directDbCheck->value)
                    ] : null,
                    'is_enabled_direct' => $directDbCheck && $directDbCheck->value > 0
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Error en verificación directa BD', ['error' => $e->getMessage()]);
            }

            // Verificar si facturación electrónica está habilitada
            Log::error('🚨 ANTES DE VALIDAR FACTURACIÓN ELECTRÓNICA', [
                'user_id' => $user->id,
                'company_id' => $this->currentCompanyId,
                'about_to_call' => 'isElectronicInvoicingEnabled'
            ]);

            $isElectronicEnabled = $this->isElectronicInvoicingEnabled($this->currentCompanyId);

            Log::error('🚨 RESULTADO VALIDACIÓN FACTURACIÓN ELECTRÓNICA', [
                'user_id' => $user->id,
                'company_id' => $this->currentCompanyId,
                'validation_result' => $isElectronicEnabled,
                'should_continue' => $isElectronicEnabled
            ]);

            Log::info('🚨 CHECKPOINT VALIDACIÓN FACTURACIÓN ELECTRÓNICA', [
                'user_id' => $user->id,
                'company_id' => $this->currentCompanyId,
                'validation_result' => $isElectronicEnabled ? 'HABILITADA' : 'DESHABILITADA',
                'should_block_sync' => !$isElectronicEnabled,
                'next_action' => $isElectronicEnabled ? 'CONTINUAR SINCRONIZACIÓN' : 'BLOQUEAR SINCRONIZACIÓN'
            ]);

            if (!$isElectronicEnabled) {
                Log::error('❌ BLOQUEANDO SINCRONIZACIÓN - Facturación electrónica DESHABILITADA', [
                    'user_id' => $user->id,
                    'company_id' => $this->currentCompanyId,
                    'option_8_value' => $this->getOptionValue(8),
                    'blocking_reason' => 'option_id=8 tiene value=0 para company_id=' . $this->currentCompanyId
                ]);
                return [
                    'success' => false,
                    'message' => '⚠️ Facturación Electrónica Deshabilitada: Para sincronizar vendedores con la API de facturación, debe activar el módulo de facturación electrónica en la configuración de la empresa.'
                ];
            }

            Log::info('✅ CONTINUANDO SINCRONIZACIÓN - Facturación electrónica HABILITADA', [
                'user_id' => $user->id,
                'company_id' => $this->currentCompanyId,
                'option_8_value' => $this->getOptionValue(8)
            ]);

            // Verificar límites del plan para vendedores (igual que en usuarios)
            if (!$this->canSyncVendors()) {
                Log::info('⏭️ Plan no permite más vendedores - omitiendo sincronización', [
                    'user_id' => $user->id,
                    'company_id' => $this->currentCompanyId
                ]);
                return [
                    'success' => false,
                    'message' => '📊 Límite del Plan Alcanzado: Ha alcanzado el máximo número de vendedores permitidos en su plan actual. Para sincronizar más vendedores, considere actualizar su plan o contacte al administrador.'
                ];
            }

            Log::info('✅ Iniciando sincronización de vendedor', [
                'user_id' => $user->id,
                'company_id' => $this->currentCompanyId
            ]);

            // Obtener usuario autenticado para configuración
            $authUser = Auth::user();
            if (!$authUser) {
                Log::error('❌ No hay usuario autenticado para sincronización');
                return [
                    'success' => false,
                    'message' => '🔐 Error de Autenticación: Su sesión ha expirado. Por favor inicie sesión nuevamente para sincronizar vendedores con la API de facturación.'
                ];
            }

            // Asegurarse de que tenancy esté correctamente inicializado
            $this->ensureTenantConnection();

            // Verificar que la conexión tenant apunte a desarrollo, no a RAP
            $tenantDbName = config('database.connections.tenant.database');
            Log::info('🔍 Verificando conexión antes de usar DatabaseConfigService', [
                'user_id' => $authUser->id,
                'tenant_db' => $tenantDbName,
                'expected_db' => 'desarrollo (NO rap)'
            ]);

            // Usar EXACTAMENTE el mismo método que funciona para items
            $optimizedConfig = DatabaseConfigService::getFacturacionConfigByUser($authUser->id);
            if (!$optimizedConfig) {
                Log::error('❌ No se encontró configuración de facturación para usuario', [
                    'user_id' => $authUser->id,
                    'user_email' => $authUser->email
                ]);
                return [
                    'success' => false,
                    'message' => '⚙️ Error de Configuración API: No se encontró la configuración de facturación electrónica para sincronizar vendedores. Verifique que la configuración esté correctamente establecida en el módulo de facturación o contacte al administrador.'
                ];
            }

            Log::info('✅ Configuración obtenida (IGUAL QUE ITEMS)', [
                'user_id' => $authUser->id,
                'config_source' => $optimizedConfig['source'] ?? 'unknown',
                'base_url' => $optimizedConfig['base_url'] ?? 'unknown',
                'has_token' => !empty($optimizedConfig['token']),
                'token_preview' => !empty($optimizedConfig['token']) ? substr($optimizedConfig['token'], 0, 10) . '...' : 'vacio',
                'username' => $optimizedConfig['username'] ?? 'unknown',
                'warehouse_id' => $optimizedConfig['warehouse_id'] ?? 'unknown'
            ]);

            // Crear ApiClient con configuración optimizada
            $apiClient = new ApiClient(
                $optimizedConfig['base_url'],
                $optimizedConfig['token'],
                $optimizedConfig['username'],
                $optimizedConfig['timeout']
            );

            Log::info('🚀 Usando configuración para Vendedores', [
                'auth_user_id' => $authUser->id,
                'warehouse_id' => $optimizedConfig['warehouse_id'],
                'source' => $optimizedConfig['source']
            ]);

            // Preparar datos para la API según estructura requerida para vendedores
            // {"name":"pedro","identification":"1018507004","observations":"vendedor","status":"active"}
            $apiData = [
                'name' => $user->name,
                'identification' => $user->contact->phone_contact ?? $user->phone, // Usar teléfono como identificación
                'observations' => 'vendedor',
                'status' => ($user->contact && $user->contact->status) ? 'active' : 'inactive'
            ];

            // LOGGING: Mostrar el JSON que se está generando
            Log::info('📋 JSON generado para API de Vendedores', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'json_data' => $apiData,
                'json_formatted' => json_encode($apiData, JSON_PRETTY_PRINT)
            ]);

            // AGREGAR LOG DE URL COMPLETA PARA DEBUGGING
            $baseUrl = $optimizedConfig['base_url'];
            Log::info('🌐 URL completa que se llamará', [
                'user_id' => $user->id,
                'base_url' => $baseUrl,
                'endpoint' => $user->api_data_id ? "sellers/{$user->api_data_id}" : 'sellers',
                'full_url' => rtrim($baseUrl, '/') . '/' . ($user->api_data_id ? "sellers/{$user->api_data_id}" : 'sellers'),
                'method' => $user->api_data_id ? 'PUT' : 'POST',
                'note' => 'Usando /sellers (correcto)'
            ]);

            // Sincronizar (crear o actualizar)
            if ($user->api_data_id) {
                Log::info('📝 Actualizando vendedor en API', [
                    'user_id' => $user->id,
                    'api_data_id' => $user->api_data_id
                ]);
                $apiResult = $apiClient->updateSeller($user->api_data_id, $apiData);
            } else {
                Log::info('📝 Creando vendedor en API', [
                    'user_id' => $user->id,
                    'name' => $user->name
                ]);
                $apiResult = $apiClient->createSeller($apiData);
            }

            if ($apiResult['success']) {
                // Guardar ID de la API si se creó exitosamente
                if (isset($apiResult['data']['id']) && !$user->api_data_id) {
                    $user->update(['api_data_id' => $apiResult['data']['id']]);
                    Log::info('💾 ID de API guardado para vendedor', [
                        'user_id' => $user->id,
                        'api_data_id' => $apiResult['data']['id']
                    ]);
                }

                Log::info('✅ Vendedor sincronizado exitosamente', [
                    'user_id' => $user->id,
                    'api_response_id' => $apiResult['data']['id'] ?? 'N/A'
                ]);

                return [
                    'success' => true,
                    'message' => '✅ Sincronización Exitosa: El vendedor ha sido sincronizado correctamente con la API de facturación electrónica.'
                ];
            } else {
                $errorMessage = $apiResult['message'] ?? 'Error desconocido en la API';
                Log::error('❌ Error sincronizando vendedor', [
                    'user_id' => $user->id,
                    'api_error' => $errorMessage
                ]);

                return [
                    'success' => false,
                    'message' => '🌐 Error de Comunicación API: No se pudo sincronizar el vendedor con la API de facturación. Detalle técnico: ' . $errorMessage
                ];
            }

        } catch (\Exception $e) {
            Log::error('❌ Excepción sincronizando vendedor', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'error_type' => 'exception'
            ]);

            return [
                'success' => false,
                'message' => '❌ Error Interno del Sistema: Ocurrió un problema inesperado durante la sincronización del vendedor. Por favor intente nuevamente o contacte al soporte técnico. Detalle: ' . $e->getMessage()
            ];
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
                $this->currentCompanyId = $companyId;
            }

            // Usar directamente el servicio de configuración que ya tiene la conexión correcta
            $optionValue = $this->getOptionValue(8);

            Log::info('🔍 VALIDACIÓN FACTURACIÓN ELECTRÓNICA', [
                'company_id' => $companyId,
                'option_id' => 8,
                'option_value' => $optionValue,
                'enabled' => $optionValue !== null && $optionValue > 0
            ]);

            // VALIDACIÓN: value > 0 significa HABILITADA
            return $optionValue !== null && $optionValue > 0;
        } catch (\Exception $e) {
            Log::error('Error verificando facturación electrónica para vendedor', [
                'company_id' => $companyId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtener configuración de facturación desde la BD del tenant
     * Combina datos centrales (usuarios) con datos del tenant (cnf_invoices)
     */
    private function getFacturacionConfigFromTenant(int $userId): ?array
    {
        try {
            // Asegurar conexión con el tenant
            $this->ensureTenantConnection();

            // Verificar conexión del tenant
            $tenantDbName = config('database.connections.tenant.database');
            Log::info('🔍 Buscando configuración de facturación en BD tenant', [
                'user_id' => $userId,
                'tenant_db_name' => $tenantDbName,
                'tenant_connection_check' => 'verificando...'
            ]);

            // Verificar que la conexión del tenant esté funcionando
            try {
                $tenantTablesCheck = DB::connection('tenant')->select("SHOW TABLES LIKE 'cnf_invoices'");
                Log::info('✅ Conexión tenant verificada', [
                    'user_id' => $userId,
                    'cnf_invoices_table_exists' => !empty($tenantTablesCheck)
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Error verificando conexión tenant', [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
                return null;
            }

            // 1. Obtener el usuario desde RAP (conexión central)
            $centralDbName = config('database.connections.central.database');

            // Obtener contacto del usuario desde RAP
            $userContact = DB::connection('central')
                ->table('users as u')
                ->join('vnt_contacts as c', 'c.id', '=', 'u.contact_id')
                ->where('u.id', $userId)
                ->select('c.warehouseId')
                ->first();

            if (!$userContact || !$userContact->warehouseId) {
                Log::warning('⚠️ Usuario sin contacto o sin warehouseId en RAP', [
                    'user_id' => $userId,
                    'contact_found' => !is_null($userContact)
                ]);
                return null;
            }

            Log::info('👤 Datos del usuario obtenidos de RAP', [
                'user_id' => $userId,
                'warehouse_id' => $userContact->warehouseId
            ]);

            // 2. Obtener configuración desde cnf_invoices del tenant usando el warehouseId
            Log::info('🔍 Ejecutando consulta en cnf_invoices del tenant', [
                'user_id' => $userId,
                'warehouse_id' => $userContact->warehouseId,
                'query' => 'SELECT * FROM cnf_invoices WHERE id_warehouses = ' . $userContact->warehouseId
            ]);

            $tenantConfig = CnfInvoice::where('id_warehouses', $userContact->warehouseId)
                ->first();

            Log::info('📋 Resultado de consulta cnf_invoices', [
                'user_id' => $userId,
                'warehouse_id' => $userContact->warehouseId,
                'config_found' => !is_null($tenantConfig),
                'config_data' => $tenantConfig ? [
                    'id' => $tenantConfig->id,
                    'facturador' => $tenantConfig->facturador,
                    'token_exists' => !empty($tenantConfig->token)
                ] : null
            ]);

            if (!$tenantConfig) {
                Log::warning('⚠️ No se encontró configuración en cnf_invoices del tenant', [
                    'user_id' => $userId,
                    'warehouse_id' => $userContact->warehouseId
                ]);
                return null;
            }

            // 3. Preparar array de configuración
            $config = [
                'base_url' => $tenantConfig->facturador, // facturador contiene la base_url
                'token' => $tenantConfig->token,
                'username' => $tenantConfig->username ?? 'api_user', // valor por defecto si no existe
                'timeout' => $tenantConfig->timeout ?? 30,
                'warehouse_id' => $userContact->warehouseId,
                'source' => 'tenant_bd'
            ];

            Log::info('✅ Configuración de facturación obtenida desde BD tenant', [
                'user_id' => $userId,
                'warehouse_id' => $config['warehouse_id'],
                'base_url' => $config['base_url'],
                'has_token' => !empty($config['token']),
                'source' => $config['source']
            ]);

            return $config;

        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo configuración de facturación desde BD tenant', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
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

        Log::info('🔧 Configurando conexión tenant', [
            'tenant_id' => $tenantId,
            'tenant_data' => $tenant->data ?? 'no data'
        ]);

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);

        // Verificar que la conexión esté correctamente configurada
        $currentTenantDb = config('database.connections.tenant.database');
        Log::info('✅ Conexión tenant configurada', [
            'tenant_id' => $tenantId,
            'tenant_database' => $currentTenantDb
        ]);
    }
}


