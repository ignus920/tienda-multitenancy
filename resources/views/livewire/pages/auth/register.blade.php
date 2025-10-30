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
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
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

    // Estados de carga y notificaciones
    public bool $isLoading = false;
    public string $loadingMessage = '';
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

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        // Activar estado de carga
        $this->isLoading = true;
        $this->loadingMessage = 'Iniciando proceso de registro...';

        // Aumentar tiempo de ejecución para creación de tenant
        set_time_limit(300); // 5 minutos

        Log::info('🚀 Iniciando proceso de registro', ['email' => $this->email]);

        $validated = $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'phone_contact' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'businessName' => ['required', 'string', 'max:255'],
            'countryId' => ['required', 'exists:countries,id'],
            'merchant_type_id' => ['required', 'exists:vnt_merchant_types,id'],
            'accept_terms' => ['required', 'accepted'],
        ]);

        Log::info('✅ Validación exitosa');

        try {
            $this->loadingMessage = 'Creando empresa...';
            DB::beginTransaction();

            // 1. Crear la empresa (vnt_companies)
            $company = VntCompany::create([
                'businessName' => $this->businessName,
                'billingEmail' => $this->email,
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'status' => 1,
                'created_at' => now(),
            ]);

            $this->loadingMessage = 'Configurando contacto principal...';

            // 2. Crear el contacto (vnt_contacts)
            $contact = VntContact::create([
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'email' => $this->email,
                'phone_contact' => $this->phone_contact,
                'status' => 1,
                'createdAt' => now(),
                'updatedAt' => now(),
            ]);

            $this->loadingMessage = 'Configurando almacén principal...';

            // 3. Crear warehouse principal (solo campos básicos sin constraints)
            $warehouse = VntWarehouse::create([
                'companyId' => $company->id,
                'name' => 'Principal',
                'address' => 'Dirección principal',
                // Quitamos status y main que pueden tener constraints
            ]);

            $this->loadingMessage = 'Creando cuenta de usuario...';

            // 4. Crear usuario
            $validated['password'] = Hash::make($validated['password']);
            $userData = [
                'name' => $this->firstName . ' ' . $this->lastName,
                'email' => $this->email,
                'password' => $validated['password'],
            ];
            $user = User::create($userData);
            event(new Registered($user));

            $this->loadingMessage = 'Configurando plan de servicio...';

            // 5. Obtener plan por defecto para el tipo de comercio
            $defaultPlain = VntPlain::where('merchantTypeId', $this->merchant_type_id)
                ->where('status', 1)
                ->first();

            $this->loadingMessage = 'Creando base de datos del tenant... (Esto puede tomar unos minutos)';

            // 6. Crear el tenant con toda la información
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

            $this->loadingMessage = 'Configurando módulos del sistema...';

            // 7. Guardar relaciones de módulos
            foreach ($this->modules as $module) {
                VntMerchantModul::firstOrCreate([
                    'merchantId' => $this->merchant_type_id,
                    'modulId' => $module->id
                ]);
            }

            DB::commit();

            // Finalizar carga con éxito
            $this->isLoading = false;
            $this->successMessage = '¡Registro completado exitosamente! Tu cuenta ha sido creada y tu base de datos está lista.';

            // TODO: Enviar token por email o WhatsApp aquí

            session()->flash('status', '¡Cuenta creada exitosamente! Se ha enviado un token de verificación.');
        } catch (\Exception $e) {
            $this->isLoading = false;
            $this->loadingMessage = '';

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

        $this->redirect(route('tenant.select', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Notificaciones de progreso y éxito -->
    @if($isLoading)
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-800">{{ $loadingMessage }}</p>
                </div>
            </div>
        </div>
    @endif

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
            <x-text-input wire:model="phone_contact" id="phone_contact" class="block mt-1 w-full" type="tel" required />
            <x-input-error :messages="$errors->get('phone_contact')" class="mt-2" />
        </div>

        <!-- Nombre del Negocio -->
        <div>
            <x-input-label for="businessName" value="Nombre del negocio" />
            <x-text-input wire:model="businessName" id="businessName" class="block mt-1 w-full" type="text" required />
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
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" required autocomplete="username" />
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

            <x-primary-button class="ml-4" :disabled="$isLoading">
                @if($isLoading)
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Procesando...
                @else
                    Registrarse
                @endif
            </x-primary-button>
        </div>
    </form>
</div>
