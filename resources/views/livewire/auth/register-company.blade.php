<div>
    <!-- El mensaje de carga ahora se maneja con SweetAlert -->

    @if($successMessage)
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center justify-between">
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
                <div class="ml-4">
                    <button type="button" wire:click="clearForm" class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors">
                        Registrar otra empresa
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Botones de prueba (TEMPORAL) - Solo se muestran si no hay mensaje de éxito -->


    <form wire:submit="register" class="space-y-4">
        <!-- Nombre y Apellido -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="firstName" class="block text-sm font-medium text-gray-700">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <x-text-input wire:model="firstName" id="firstName" class="block mt-1 w-full" type="text" required autofocus />
                <x-input-error :messages="$errors->get('firstName')" class="mt-2" />
            </div>
            <div>
                <label for="lastName" class="block text-sm font-medium text-gray-700">
                    Apellido <span class="text-red-500">*</span>
                </label>
                <x-text-input wire:model="lastName" id="lastName" class="block mt-1 w-full" type="text" required />
                <x-input-error :messages="$errors->get('lastName')" class="mt-2" />
            </div>
        </div>

        <!-- Teléfono de Contacto -->
        <div>
            <label for="phone_contact" class="block text-sm font-medium text-gray-700">
                Número celular <span class="text-red-500">*</span>
            </label>
            <x-text-input wire:model.lazy="phone_contact" id="phone_contact" class="block mt-1 w-full" type="number" required />
            <x-input-error :messages="$errors->get('phone_contact')" class="mt-2" />
        </div>

        <!-- Nombre del Negocio -->
        <div>
            <label for="businessName" class="block text-sm font-medium text-gray-700">
                Nombre del negocio <span class="text-red-500">*</span>
            </label>
            <x-text-input wire:model.lazy="businessName" id="businessName" class="block mt-1 w-full" type="text" required />
            <x-input-error :messages="$errors->get('businessName')" class="mt-2" />
        </div>

        <!-- País -->
       @livewire('selects.country-select', [
       'countryId' => $countryId,
       'name' => 'countryId',
       'label' => 'País',
        'placeholder' => 'Selecciona el país',
         'class' => 'block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm'
    ])

        <!-- Tipo de Negocio -->
        <div>
            <label for="merchant_type_id" class="block text-sm font-medium text-gray-700">
                Tipo de negocio escogido <span class="text-red-500">*</span>
            </label>
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
            <label for="email" class="block text-sm font-medium text-gray-700">
                Email <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <x-text-input
                    wire:model.live="email"
                    id="email"
                    class="block mt-1 w-full pr-10"
                    type="email"
                    placeholder="usuario@ejemplo.com"
                    required
                    autocomplete="username"
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    @if($email && !$errors->has('email'))
                        <!-- Email válido -->
                        <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @elseif($email && $errors->has('email'))
                        <!-- Email con errores -->
                        <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @else
                        <!-- Sin email o estado neutral -->
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    @endif
                </div>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
            @if(!$errors->has('email') && !$email)
                <p class="mt-1 text-sm text-gray-500">Ingresa tu correo electrónico (ej: nombre@empresa.com)</p>
            @endif
        </div>

        <!-- Contraseña -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                Contraseña <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <x-text-input wire:model.live="password" id="password" class="block mt-1 w-full pr-10" type="password" required autocomplete="new-password" />
                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePasswordVisibility('password')">
                    <svg id="password-eye-open" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="password-eye-closed" class="h-5 w-5 text-gray-400 hover:text-gray-600 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L5.64 5.64m4.242 4.242L15.14 15.14m0 0l4.243 4.243M15.14 15.14L19.5 19.5" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar Contraseña -->
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                Confirmar Contraseña <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <x-text-input wire:model.live="password_confirmation" id="password_confirmation" class="block mt-1 w-full pr-10" type="password" required autocomplete="new-password" />
                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePasswordVisibility('password_confirmation')">
                    <svg id="password_confirmation-eye-open" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="password_confirmation-eye-closed" class="h-5 w-5 text-gray-400 hover:text-gray-600 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L5.64 5.64m4.242 4.242L15.14 15.14m0 0l4.243 4.243M15.14 15.14L19.5 19.5" />
                    </svg>
                </button>
            </div>
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

        <div class="flex items-center justify-between pt-12">
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

@push('scripts')
<script>
console.log('🔧 Script cargado - Inicializando listeners...');

let loadingSwal = null;
let loadingStartTime = null;

document.addEventListener('livewire:init', () => {
    console.log('✅ Livewire inicializado - Configurando listeners');

    // Listener para mostrar loading cuando inicie el registro
    Livewire.on('registration-started', () => {
        console.log('⏳ EVENTO RECIBIDO: registration-started - Mostrando SweetAlert de carga...');

        // Guardar el tiempo de inicio
        loadingStartTime = Date.now();

        // Cerrar cualquier SweetAlert existente primero
        if (loadingSwal) {
            loadingSwal.close();
        }

        loadingSwal = Swal.fire({
            title: 'Procesando registro...',
            html: `
                <div class="text-center">
                    <div class="mb-4">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>
                    <p class="text-gray-600">Esto puede tomar unos minutos mientras creamos tu tenant.</p>
                    <p class="text-sm text-gray-500 mt-2">Por favor no cierres esta ventana.</p>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                console.log('🎯 SweetAlert de carga ABIERTO correctamente');
                Swal.showLoading();
            }
        });

        console.log('✅ SweetAlert de carga configurado:', loadingSwal);
    });

    // Listener para completar el registro
    Livewire.on('registration-complete', (event) => {
        console.log('🎉 Evento registration-complete recibido:', event);

        const { title, message, redirectUrl } = event[0];
        console.log('📝 Datos del evento:', { title, message, redirectUrl });

        // Calcular cuánto tiempo ha pasado desde que se inició la carga
        const elapsedTime = loadingStartTime ? Date.now() - loadingStartTime : 0;
        const minimumLoadingTime = 2000; // 2 segundos mínimo
        const remainingTime = Math.max(0, minimumLoadingTime - elapsedTime);

        console.log(`⏱️ Tiempo transcurrido: ${elapsedTime}ms, esperando ${remainingTime}ms más`);

        // Función para mostrar el resultado
        const showSuccessAlert = () => {
            // Cerrar el SweetAlert de carga si existe
            if (loadingSwal) {
                console.log('🔄 Cerrando SweetAlert de carga...');
                loadingSwal.close();
            }

            Swal.fire({
                title: title,
                text: message,
                icon: 'success',
                confirmButtonText: 'Continuar',
                allowOutsideClick: false,
                allowEscapeKey: false,
            }).then((result) => {
                console.log('👆 Usuario hizo clic en la alerta:', result);
                if (result.isConfirmed) {
                    console.log('🔄 Redirigiendo a:', redirectUrl);
                    window.location.href = redirectUrl;
                }
            });
        };

        // Si no ha pasado suficiente tiempo, esperar
        if (remainingTime > 0) {
            console.log(`⏳ Esperando ${remainingTime}ms para mostrar mejor UX...`);
            setTimeout(showSuccessAlert, remainingTime);
        } else {
            showSuccessAlert();
        }
    });

    // Listener para errores en el registro
    Livewire.on('registration-error', (event) => {
        console.log('❌ Error en el registro:', event);

        const { title, message } = event[0];

        // Calcular cuánto tiempo ha pasado desde que se inició la carga
        const elapsedTime = loadingStartTime ? Date.now() - loadingStartTime : 0;
        const minimumLoadingTime = 1000; // 1 segundo mínimo para errores
        const remainingTime = Math.max(0, minimumLoadingTime - elapsedTime);

        // Función para mostrar el error
        const showErrorAlert = () => {
            // Cerrar el SweetAlert de carga si existe
            if (loadingSwal) {
                console.log('🔄 Cerrando SweetAlert de carga por error...');
                loadingSwal.close();
            }

            Swal.fire({
                title: title || 'Error en el registro',
                text: message || 'Ha ocurrido un error inesperado. Por favor intenta nuevamente.',
                icon: 'error',
                confirmButtonText: 'Entendido',
                allowOutsideClick: true,
                allowEscapeKey: true,
            });
        };

        // Si no ha pasado suficiente tiempo, esperar un poco
        if (remainingTime > 0) {
            setTimeout(showErrorAlert, remainingTime);
        } else {
            showErrorAlert();
        }
    });
});

// Función de prueba manual para loading
window.testLoadingAlert = function() {
    console.log('🧪 Probando alerta de carga...');
    Swal.fire({
        title: 'Cargando...',
        html: `
            <div class="text-center">
                <div class="mb-4">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
                <p class="text-gray-600">Procesando...</p>
            </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

// Función de prueba manual para success
window.testAlert = function() {
    console.log('🧪 Probando alerta de éxito...');
    Swal.fire({
        title: 'Prueba',
        text: 'Esta es una prueba manual',
        icon: 'success',
        confirmButtonText: 'OK'
    });
};

// Función para probar el flujo completo
window.testFullFlow = function() {
    console.log('🧪 Probando flujo completo...');

    // Simular inicio de registro
    Livewire.dispatch('registration-started');

    // Después de 3 segundos, simular éxito
    setTimeout(() => {
        Livewire.dispatch('registration-complete', [{
            title: '¡Registro Exitoso!',
            message: 'Tu cuenta ha sido creada. Revisa tu correo (o WhatsApp) para obtener tu código de verificación.',
            redirectUrl: '#'
        }]);
    }, 3000);
};

console.log('🎯 Para probar: testLoadingAlert(), testAlert() o testFullFlow()');

// Función para alternar visibilidad de contraseñas
window.togglePasswordVisibility = function(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const eyeOpen = document.getElementById(fieldId + '-eye-open');
    const eyeClosed = document.getElementById(fieldId + '-eye-closed');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        passwordField.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
};
</script>
@endpush