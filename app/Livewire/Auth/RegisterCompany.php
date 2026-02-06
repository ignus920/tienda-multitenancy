<?php

namespace App\Livewire\Auth;

use App\Models\Auth\User;
use App\Models\Central\VntMerchantType;
use App\Models\Central\VntCompany;
use App\Models\Central\VntContact;
use App\Models\Central\VntWarehouse;
use App\Models\Central\VntPlain;
use App\Models\Central\CnfCountry;
use App\Services\Tenant\TenantManager;
use App\Services\WhatsApp\WhatsAppService;
use App\Http\Traits\HasCommonValidation;
use App\Mail\WhatsAppTokenMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class RegisterCompany extends Component
{
    use HasCommonValidation;

    protected $listeners = ['country-selected' => 'updateCountry'];

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
    public $plain_id = null;  // ← NUEVO: Para que el usuario seleccione el plan

    // Datos de aceptación
    public bool $accept_terms = false;

    // Estados de notificaciones
    public string $successMessage = '';

    // Estados de progreso UX
    public bool $isRegistering = false;
    public string $currentStep = '';
    public int $progressPercentage = 0;

    // Colecciones
    public $merchant_types = [];
    public $countries = [];
    public $plains = [];

    public function mount()
    {
        $this->merchant_types = VntMerchantType::where('status', 1)->get();
        $this->countries = CnfCountry::where('status', 1)->get();
        // Los planes se cargarán dinámicamente cuando se seleccione el merchant_type
        $this->plains = collect(); // Iniciar vacío
    }

    public function updatedMerchantTypeId()
    {
        // Filtrar planes según el tipo de comercio seleccionado
        $this->plains = VntPlain::where('status', 1)
            ->where('merchantTypeId', $this->merchant_type_id)
            ->get();
        
        // Resetear el plan seleccionado cuando cambia el tipo de comercio
        $this->plain_id = null;
        
        Log::info('📋 Planes cargados para merchant_type', [
            'merchant_type_id' => $this->merchant_type_id,
            'plains_count' => $this->plains->count()
        ]);
    }

    public function updateCountry($countryId)
    {
        $this->countryId = $countryId;
        Log::info('País seleccionado', ['countryId' => $countryId]);
    }

    public function updatedEmail()
    {
        $this->validateEmailRealtime();
    }

    public function updatedPhoneContact()
    {
        $this->validatePhoneRealtime();
    }

    public function updatedBusinessName()
    {
        $this->validateBusinessNameRealtime();
    }

    public function updatedPassword()
    {
        $this->validatePasswordRealtime();
    }

    public function updatedPasswordConfirmation()
    {
        $this->validatePasswordConfirmationRealtime();
    }

    /**
     * Método de prueba para verificar que los mensajes funcionen
     */
    public function testMessages()
    {
        $this->isRegistering = true;
        $this->currentStep = 'Probando mensaje de carga...';

        Log::info('Método testMessages ejecutado', [
            'isRegistering' => $this->isRegistering,
            'currentStep' => $this->currentStep
        ]);
    }

    public function testSuccess()
    {
        $this->isRegistering = false;
        $this->successMessage = 'Mensaje de éxito funcionando correctamente!';

        Log::info('Método testSuccess ejecutado', [
            'isRegistering' => $this->isRegistering,
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

        Log::info('🚀 Iniciando registro');
        Log::info('🔍 Validando información del registro');
        
        $this->isRegistering = true;
        
        // Validar que se seleccionó un plan
        if (!$this->plain_id) {
            $this->isRegistering = false;
            $this->dispatch('registration-error', [
                'title' => 'Plan Requerido',
                'message' => 'Debes seleccionar un plan de servicio.'
            ]);
            return;
        }
        
        $validated = $this->validateRegistration();

        try {
            DB::beginTransaction();

            Log::info('📝 Creando empresa');    
            // 1. Crear la empresa
            $company = VntCompany::create([
                'businessName' => $this->businessName,
                'billingEmail' => $this->email,
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'status' => 1,
                'created_at' => now(),
            ]);

            Log::info('👤 Creando contacto');
            $contact = VntContact::create([
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'email' => $this->email,
                'phone_contact' => $this->phone_contact,
                'status' => 1,
                'createdAt' => now(),
                'updatedAt' => now(),
            ]);

            // 3. Crear warehouse principal
            Log::info('🏢 Creando warehouse principal');
            $warehouse = VntWarehouse::create([
                'companyId' => $company->id,
                'name' => 'Principal',
                'address' => 'Dirección principal',
            ]);

            // Paso 3: Crear usuario
            Log::info('🔐 Creando usuario');
            $validated['password'] = Hash::make($validated['password']);
            $userData = [
                'name' => $this->firstName . ' ' . $this->lastName,
                'email' => $this->email,
                'phone' => $this->phone_contact,
                'password' => $validated['password'],
                'profile_id' => 2,
                'contact_id' => $contact->id,
            ];
            $user = User::create($userData);
            event(new Registered($user));

            // Paso 4: Generar códigos de verificación
            Log::info('📧 Generando código de verificación');
            $whatsappToken = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $user->update([
                'whatsapp_token' => $whatsappToken,
                'whatsapp_token_expires_at' => now()->addMinutes(15),
            ]);

            // Enviar por WhatsApp
            try {
                $whatsappService = app(WhatsAppService::class);
                $whatsappService->enviarCodigoVerificacion(
                    $this->phone_contact,
                    $this->firstName . ' ' . $this->lastName,
                    $whatsappToken,
                    config('whatsapp.empresa.telefono'),
                    config('whatsapp.empresa.nombre')
                );
            } catch (\Exception $e) {
                // No interrumpir registro por errores de WhatsApp
            }

            // Enviar por email
            try {

                Mail::to($this->email)->send(new WhatsAppTokenMail(
                    $this->firstName . ' ' . $this->lastName,
                    $whatsappToken
                ));

                Log::info('✅ Código enviado por correo exitosamente', [
                    'user_email' => $this->email
                ]);

            } catch (\Exception $e) {
                Log::error('❌ Error enviando email', [
                    'error' => $e->getMessage(),
                    'email' => $this->email
                ]);
                // No interrumpimos el registro por errores de email
            }

            // 5. Validar que se seleccionó un plan
            if (!$this->plain_id) {
                throw new \Exception('Debes seleccionar un plan de servicio.');
            }
            
            Log::info('� Plan seleccionado por el usuario', [
                'plain_id' => $this->plain_id,
                'merchant_type_id' => $this->merchant_type_id
            ]);

            // 5.1. Verificar que el plan existe y está activo
            $selectedPlain = VntPlain::where('id', $this->plain_id)
                ->where('merchantTypeId', $this->merchant_type_id)
                ->where('status', 1)
                ->first();

            if (!$selectedPlain) {
                Log::error('❌ Plan seleccionado no válido', [
                    'plain_id' => $this->plain_id,
                    'merchant_type_id' => $this->merchant_type_id
                ]);
                throw new \Exception('El plan seleccionado no es válido.');
            }

            Log::info('✅ Plan validado correctamente', [
                'plain_id' => $selectedPlain->id,
                'plain_name' => $selectedPlain->name
            ]);

            // 6. Crear SOLO el registro del tenant (sin base de datos física)
            Log::info('📝 Creando registro del tenant (sin base de datos física para agilizar el proceso)');
            $tenantManager = app(TenantManager::class);
            $tenant = $tenantManager->createTenantRecord([
                'name' => $this->businessName,
                'email' => $this->email,
                'company_id' => $company->id,
                'merchant_type_id' => $this->merchant_type_id,
                'plain_id' => $this->plain_id,  // ← Usar el plan seleccionado por el usuario
                'afiliation_date' => now(),
                'end_test' => now()->addDays(30), // 30 días de prueba
            ], $user);

            // 7. Los módulos se asignan solo por administradores globales
            // No creamos automáticamente registros en vnt_merchant_moduls
            Log::info('🔧 Registro del tenant creado exitosamente. La base de datos se configurará cuando completes tus datos de empresa.');

            DB::commit();

            // Finalizar con éxito
            $this->successMessage = '¡Registro completado exitosamente! Tu cuenta ha sido creada. La base de datos se configurará cuando completes los datos de tu empresa.';

            // TODO: Enviar token por email o WhatsApp aquí

            Log::info('✅ Registro completado - Preparando para emitir evento registration-complete');
            session()->flash('status', '¡Cuenta creada exitosamente! Se ha enviado un token de verificación.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al crear registro completo: ' . $e->getMessage(), [
                'email' => $this->email,
                'businessName' => $this->businessName,
                'trace' => $e->getTraceAsString()
            ]);

            // Emitir evento de error para cerrar loading y mostrar error
            $this->dispatch('registration-error', [
                'title' => 'Error en el registro',
                'message' => 'Error al crear la cuenta: ' . $e->getMessage()
            ]);

            session()->flash('error', 'Error al crear la cuenta: ' . $e->getMessage());
            return;
        }

        // Log::info('🎉 Registro completado exitosamente', ['user_id' => $user->id, 'email' => $user->email]);

        // NO hacer autologin automático - el usuario debe verificar primero
        // Auth::login($user);

        Log::info('✅ Registro completado - Redirigiendo a verificación de token');
        session()->flash('status', '¡Cuenta creada exitosamente! Se ha enviado un código de verificación a tu correo y WhatsApp.');
        
        // Redirigir directamente a verificación de token
        $this->redirect(route('verify-token'));
    }

    /**
     * Limpiar todos los campos del formulario
     */
    private function clearForm(): void
    {
        Log::info('🧹 Limpiando formulario después del registro exitoso');

        // Limpiar datos del contacto
        $this->firstName = '';
        $this->lastName = '';
        $this->phone_contact = '';
        $this->email = '';
        $this->password = '';
        $this->password_confirmation = '';

        // Limpiar datos de la empresa
        $this->businessName = '';
        $this->countryId = null;
        $this->merchant_type_id = null;

        // Limpiar datos de aceptación
        $this->accept_terms = false;

        // Reset estados de progreso
        $this->isRegistering = false;
        $this->progressPercentage = 0;
        $this->currentStep = '';

        Log::info('✅ Formulario limpiado correctamente');
    }

    public function render()
    {
        return view('livewire.auth.register-company');
    }
}