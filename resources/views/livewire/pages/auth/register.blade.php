<?php

use App\Models\Auth\User;
use App\Models\Central\VntMerchantType;
use App\Models\Central\VntModul;
use App\Models\Central\VntMerchantModul;
use App\Models\Central\VntCompany;
use App\Models\Central\VntContact;
use App\Models\Central\VntWarehouse;
use App\Models\Central\VntPlain;
use App\Models\Central\CnfCountry;
use App\Services\Tenant\TenantManager;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Livewire\Volt\Component;

new class extends Component
{
    // Datos del contacto
    public string $firstName = '';
    public string $lastName = '';
    public string $phone_contact = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // Datos de la empresa
    public string $businessName = '';
    public $countryId = null;
    public $merchant_type_id = null;

    // Datos de aceptación
    public bool $accept_terms = false;

    // Estados de notificaciones
    public string $successMessage = '';

    // Colecciones
    public $merchant_types = [];
    public $modules = [];
    public $countries = [];
    public $plains = [];

    public function mount()
    {
        $this->merchant_types = VntMerchantType::where('status', 1)->get();
        $this->modules = VntModul::where('status', 1)->get();
        $this->countries = CnfCountry::where('status', 1)->get();
        $this->plains = VntPlain::where('status', 1)->get();
    }

    public function updatedMerchantTypeId()
    {
        // Filtrar planes según el tipo de comercio seleccionado
        $this->plains = VntPlain::where('status', 1)
            ->where('merchantTypeId', $this->merchant_type_id)
            ->get();
    }

    public function updatedEmail()
    {
        Log::info('=== updatedEmail ejecutándose ===', ['email' => $this->email]);

        // Limpiar errores si el campo está vacío
        if (empty($this->email)) {
            Log::info('Email vacío, saltando validación');
            return;
        }

        try {
            // Validar email automáticamente cuando el usuario termine de escribir
            $this->validateOnly('email', [
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email', 'unique:vnt_contacts,email']
            ], [
                'email.unique' => 'Este correo electrónico ya está registrado en el sistema.',
            ]);
            Log::info('Email validado correctamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::info('Error de validación capturado (esto es normal)', ['errors' => $e->errors()]);
            throw $e; // Re-lanzar para que Livewire lo maneje
        }
    }

    public function updatedPhoneContact()
    {
        // Limpiar errores si el campo está vacío
        if (empty($this->phone_contact)) {
            return;
        }

        // Validar teléfono automáticamente cuando el usuario termine de escribir
        $this->validateOnly('phone_contact', [
            'phone_contact' => ['required', 'string', 'max:20', 'unique:vnt_contacts,phone_contact']
        ], [
            'phone_contact.unique' => 'Este número de teléfono ya está registrado en el sistema.',
        ]);
    }

    public function updatedBusinessName()
    {
        // Limpiar errores si el campo está vacío
        if (empty($this->businessName)) {
            return;
        }

        // Validar nombre del negocio automáticamente cuando el usuario termine de escribir
        $this->validateOnly('businessName', [
            'businessName' => ['required', 'string', 'max:255', 'unique:vnt_companies,businessName']
        ], [
            'businessName.unique' => 'Ya existe una empresa registrada con este nombre.',
        ]);
    }

    /**
     * Método de prueba para verificar que los mensajes funcionen
     */
    public function testMessages()
    {
        $this->isLoading = true;
        $this->loadingMessage = 'Probando mensaje de carga...';

        Log::info('Método testMessages ejecutado', [
            'isLoading' => $this->isLoading,
            'loadingMessage' => $this->loadingMessage
        ]);
    }

    public function testSuccess()
    {
        $this->isLoading = false;
        $this->successMessage = 'Mensaje de éxito funcionando correctamente!';

        Log::info('Método testSuccess ejecutado', [
            'isLoading' => $this->isLoading,
            'successMessage' => $this->successMessage
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        // Aumentar tiempo de ejecución para creación de tenant
        set_time_limit(300); // 5 minutos

        Log::info('🚀 Iniciando proceso de registro', ['email' => $this->email]);

        $validated = $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'phone_contact' => ['required', 'string', 'max:20', 'unique:vnt_contacts,phone_contact'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class.',email', 'unique:vnt_contacts,email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'businessName' => ['required', 'string', 'max:255', 'unique:vnt_companies,businessName'],
            'countryId' => ['required', 'exists:countries,id'],
            'merchant_type_id' => ['required', 'exists:vnt_merchant_types,id'],
            'accept_terms' => ['required', 'accepted'],
        ], [
            'email.unique' => 'Este correo electrónico ya está registrado en el sistema.',
            'phone_contact.unique' => 'Este número de teléfono ya está registrado en el sistema.',
            'businessName.unique' => 'Ya existe una empresa registrada con este nombre.',
            'accept_terms.accepted' => 'Debe aceptar los términos y condiciones para continuar.',
        ]);

        Log::info('✅ Validación exitosa');

        try {
            Log::info('💼 Creando empresa...');

            DB::beginTransaction();
            Log::info('🗄️ Transacción iniciada');

            // 1. Crear la empresa (vnt_companies)
            Log::info('🏢 Creando empresa en vnt_companies');
            $company = VntCompany::create([
                'businessName' => $this->businessName,
                'billingEmail' => $this->email,
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'status' => 1,
                'created_at' => now(),
            ]);

            // 2. Crear el contacto (vnt_contacts)
            Log::info('👤 Configurando contacto principal...');
            $contact = VntContact::create([
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'email' => $this->email,
                'phone_contact' => $this->phone_contact,
                'status' => 1,
                'createdAt' => now(),
                'updatedAt' => now(),
            ]);

            // 3. Crear warehouse principal (solo campos básicos sin constraints)
            Log::info('🏪 Configurando almacén principal...');
            $warehouse = VntWarehouse::create([
                'companyId' => $company->id,
                'name' => 'Principal',
                'address' => 'Dirección principal',
                // Quitamos status y main que pueden tener constraints
            ]);

            // 4. Crear usuario
            Log::info('👨‍💻 Creando cuenta de usuario...');
            $validated['password'] = Hash::make($validated['password']);
            $userData = [
                'name' => $this->firstName . ' ' . $this->lastName,
                'email' => $this->email,
                'password' => $validated['password'],
            ];
            $user = User::create($userData);
            event(new Registered($user));

            // 5. Obtener plan por defecto para el tipo de comercio
            Log::info('📋 Configurando plan de servicio...');
            $defaultPlain = VntPlain::where('merchantTypeId', $this->merchant_type_id)
                ->where('status', 1)
                ->first();

            // 6. Crear el tenant con toda la información
            Log::info('🗄️ Creando base de datos del tenant... (Esto puede tomar unos minutos)');
            $tenantManager = app(TenantManager::class);
            $tenant = $tenantManager->create([
                'name' => $this->businessName,
                'email' => $this->email,
                'company_id' => $company->id,
                'merchant_type_id' => $this->merchant_type_id,
                'plain_id' => $defaultPlain?->id,
                'afiliation_date' => now(),
                'end_test' => now()->addDays(30), // 30 días de prueba
            ], $user);

            // 7. Guardar relaciones de módulos
            Log::info('🔧 Configurando módulos del sistema...');
            foreach ($this->modules as $module) {
                VntMerchantModul::firstOrCreate([
                    'merchantId' => $this->merchant_type_id,
                    'modulId' => $module->id
                ]);
            }

            DB::commit();

            // Finalizar con éxito
            $this->successMessage = '¡Registro completado exitosamente! Tu cuenta ha sido creada y tu base de datos está lista.';

            // TODO: Enviar token por email o WhatsApp aquí

            session()->flash('status', '¡Cuenta creada exitosamente! Se ha enviado un token de verificación.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear registro completo: ' . $e->getMessage(), [
                'email' => $this->email,
                'businessName' => $this->businessName,
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error al crear la cuenta: ' . $e->getMessage());
            return;
        }

        Log::info('🎉 Registro completado exitosamente', ['user_id' => $user->id, 'email' => $user->email]);

        Auth::login($user);

        // Redirigir a completar datos de empresa en lugar del tenant select
        $this->redirect(route('company.setup'), navigate: true);
    }
}; ?>

<div>
    <!-- Notificaciones de progreso y éxito usando wire:loading -->
    <div wire:loading wire:target="register" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-blue-800">
                    Procesando registro... Esto puede tomar unos minutos mientras creamos tu tenant.
                    <br><span class="text-xs">Por favor no cierres esta ventana.</span>
                </p>
            </div>
        </div>
    </div>

    @if($successMessage)
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ $successMessage }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Botones de prueba (TEMPORAL) -->
    <!-- <div class="mb-4 p-2 bg-yellow-50 border border-yellow-200 rounded">
        <p class="text-xs text-yellow-700 mb-2">Pruebas de mensajes (solo desarrollo):</p>
        <button type="button" wire:click="testMessages" class="mr-2 px-2 py-1 bg-blue-500 text-white text-xs rounded">
            Probar Loading
        </button>
        <button type="button" wire:click="testSuccess" class="px-2 py-1 bg-green-500 text-white text-xs rounded">
            Probar Success
        </button>
    </div> -->

    <form wire:submit="register" class="space-y-4">
        <!-- Nombre y Apellido -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="firstName" value="Nombre" />
                <x-text-input wire:model="firstName" id="firstName" class="block mt-1 w-full" type="text" required autofocus />
                <x-input-error :messages="$errors->get('firstName')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="lastName" value="Apellido" />
                <x-text-input wire:model="lastName" id="lastName" class="block mt-1 w-full" type="text" required />
                <x-input-error :messages="$errors->get('lastName')" class="mt-2" />
            </div>
        </div>

        <!-- Teléfono de Contacto -->
        <div>
            <x-input-label for="phone_contact" value="Número telefónico" />
            <x-text-input wire:model.lazy="phone_contact" id="phone_contact" class="block mt-1 w-full" type="tel" required />
            <x-input-error :messages="$errors->get('phone_contact')" class="mt-2" />
        </div>

        <!-- Nombre del Negocio -->
        <div>
            <x-input-label for="businessName" value="Nombre del negocio" />
            <x-text-input wire:model.lazy="businessName" id="businessName" class="block mt-1 w-full" type="text" required />
            <x-input-error :messages="$errors->get('businessName')" class="mt-2" />
        </div>

        <!-- País -->
        <div>
            <x-input-label for="countryId" value="País" />
            <select wire:model="countryId" id="countryId" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">Selecciona el país</option>
                @foreach($countries as $country)
                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('countryId')" class="mt-2" />
        </div>

        <!-- Tipo de Negocio -->
        <div>
            <x-input-label for="merchant_type_id" value="Tipo de negocio escogido" />
            <select wire:model="merchant_type_id" id="merchant_type_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">Selecciona el tipo de negocio</option>
                @foreach($merchant_types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }} - {{ $type->description }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('merchant_type_id')" class="mt-2" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input wire:model.lazy="email" id="email" class="block mt-1 w-full" type="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Contraseña -->
        <div>
            <x-input-label for="password" value="Contraseña" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar Contraseña -->
        <div>
            <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Aceptar Política de Tratamiento de Datos -->
        <div class="flex items-center">
            <input wire:model="accept_terms" id="accept_terms" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" required>
            <label for="accept_terms" class="ml-2 block text-sm text-gray-900">
                Acepto la <a href="#" class="text-indigo-600 hover:text-indigo-500">política de tratamiento de datos</a>
            </label>
        </div>
        <x-input-error :messages="$errors->get('accept_terms')" class="mt-2" />

        <div class="flex items-center justify-between pt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}" wire:navigate>
                ¿Ya estás registrado?
            </a>

            <x-primary-button class="ml-4" wire:loading.attr="disabled">
                <svg wire:loading wire:target="register" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="register">Registrarse</span>
                <span wire:loading wire:target="register">Procesando registro...</span>
            </x-primary-button>
        </div>
    </form>
</div>
