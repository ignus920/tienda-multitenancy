<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Completar Datos de Empresa</h1>
            <p class="mt-2 text-gray-600">Complete la información faltante de su empresa</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Progreso</span>
                <span class="text-sm font-medium text-gray-700">{{ $this->getProgressPercentage() }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: {{ $this->getProgressPercentage() }}%"></div>
            </div>
        </div>

        <!-- Step Indicator -->
        <div class="flex justify-center mb-8">
            <div class="flex items-center space-x-4">
                @for($i = 1; $i <= $totalSteps; $i++)
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full
                            {{ $currentStep >= $i ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600' }}">
                            {{ $i }}
                        </div>
                        @if($i < $totalSteps)
                            <div class="w-12 h-1 mx-2 {{ $currentStep > $i ? 'bg-indigo-600' : 'bg-gray-300' }}"></div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>

        <!-- Success Message -->
        @if($successMessage)
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <p class="text-green-800 font-medium">{{ $successMessage }}</p>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <form wire:submit.prevent="{{ $currentStep == $totalSteps ? 'finish' : 'nextStep' }}">
                <div class="px-6 py-8">
                    <!-- Step 1: Datos Fiscales de la Empresa -->
                    @if($currentStep == 1)
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Datos Fiscales de la Empresa</h2>

                            <div class="space-y-6">
                                <!-- Tipo de Identificación (Primero) -->
                                @livewire('selects.type-identification-select', [
                                    'typeIdentificationId' => $typeIdentificationId,
                                    'name' => 'typeIdentificationId'
                                ])

                                <!-- NIT/Identificación con campo DV condicional -->
                                @if($typeIdentificationId > 0)
                                    @if($typeIdentificationId == 2)
                                        <!-- NIT con DV -->
                                        <div class="grid grid-cols-3 gap-4">
                                            <div class="col-span-2">
                                                <label for="identification" class="block text-sm font-medium text-gray-700">NIT *</label>
                                                <input wire:model.live="identification" type="text" id="identification" maxlength="15"
                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                    placeholder="123456789" required>
                                                @error('identification') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label for="verification_digit" class="block text-sm font-medium text-gray-700">DV *</label>
                                                <input wire:model="verification_digit" type="text" id="verification_digit" maxlength="1"
                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                    placeholder="5">
                                                @error('verification_digit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    @else
                                        <!-- Otros tipos de identificación -->
                                        <div>
                                            <label for="identification" class="block text-sm font-medium text-gray-700">Número de Identificación *</label>
                                            <input wire:model.live="identification" type="text" id="identification" maxlength="15"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                            @error('identification') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                @endif

                                <!-- Tipo de Persona -->
                                <div>
                                    <label for="typePerson" class="block text-sm font-medium text-gray-700">Tipo de Persona *</label>
                                    <select wire:model.live="typePerson" id="typePerson"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="Natural">Persona Natural</option>
                                        <option value="Juridica">Persona Jurídica</option>
                                    </select>
                                    @error('typePerson') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Campos condicionales según tipo de persona -->
                                @if($typePerson)
                                    @if($typePerson == 'Natural')
                                        <!-- Persona Natural: Nombre y Apellido -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="firstName" class="block text-sm font-medium text-gray-700">Nombre *</label>
                                                <input wire:model="firstName" type="text" id="firstName"
                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                    placeholder="Ingrese su nombre">
                                                @error('firstName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label for="lastName" class="block text-sm font-medium text-gray-700">Apellido *</label>
                                                <input wire:model="lastName" type="text" id="lastName"
                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                    placeholder="Ingrese su apellido">
                                                @error('lastName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    @elseif($typePerson == 'Juridica')
                                        <!-- Persona Jurídica: Razón Social -->
                                        <div>
                                            <label for="businessName" class="block text-sm font-medium text-gray-700">Razón Social *</label>
                                            <input wire:model="businessName" type="text" id="businessName"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Ingrese la razón social de la empresa">
                                            @error('businessName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                @endif

                                <!-- Código CIIU -->
                                <div>
                                    <label for="code_ciiu" class="block text-sm font-medium text-gray-700">Código CIIU *</label>
                                    <input wire:model="code_ciiu" type="text" id="code_ciiu"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="ej: 4711">
                                    @error('code_ciiu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Selects para configuraciones fiscales -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @livewire('selects.regime-select', [
                                        'regimeId' => $regimeId,
                                        'name' => 'regimeId',
                                        'label' => 'Régimen',
                                        'placeholder' => 'Seleccionar régimen'
                                    ])

                                    @livewire('selects.fiscal-responsibility-select', [
                                        'fiscalResponsibilityId' => $this->fiscalResponsibilityId,
                                        'name' => 'fiscalResponsibilityId',
                                        'label' => 'Responsabilidad Fiscal',
                                        'placeholder' => 'Seleccionar responsabilidad fiscal'
                                    ])
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Step 2: Datos de Sucursales -->
                    @if($currentStep == 2)
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Configuración de Sucursales</h2>

                            <div class="space-y-6">
                                <!-- Pregunta sobre múltiples sucursales -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-4">¿Tiene más sucursales? *</label>
                                    <div class="flex space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" wire:model.live="hasMultipleBranches" value="1"
                                                class="form-radio h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                            <span class="ml-2 text-sm text-gray-700">Sí</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" wire:model.live="hasMultipleBranches" value="0"
                                                class="form-radio h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                            <span class="ml-2 text-sm text-gray-700">No</span>
                                        </label>
                                    </div>
                                    @error('hasMultipleBranches') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Nombre de la sucursal (condicional) -->
                                @if($hasMultipleBranches)
                                    <div>
                                        <label for="branchName" class="block text-sm font-medium text-gray-700">Nombre de la Sucursal *</label>
                                        <input wire:model="branchName" type="text" id="branchName"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="Ingrese el nombre de la sucursal">
                                        @error('branchName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                                <!-- Datos de Ubicación del Warehouse -->
                                <div class="border-t pt-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Datos de Ubicación</h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <!-- Dirección -->
                                        <div class="md:col-span-2">
                                            <label for="address" class="block text-sm font-medium text-gray-700">Dirección *</label>
                                            <input wire:model="address" type="text" id="address"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Dirección completa de la sucursal">
                                            @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Ciudad -->
                                        @livewire('selects.city-select', [
                                            'cityId' => $cityId,
                                            'countryId' => 48,
                                            'name' => 'cityId'
                                        ])

                                        <!-- Código Postal -->
                                        <div>
                                            <label for="postcode" class="block text-sm font-medium text-gray-700">Código Postal *</label>
                                            <input wire:model="postcode" type="text" id="postcode" maxlength="10"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                placeholder="Código postal">
                                            @error('postcode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Form Actions -->
                <div class="px-6 py-6 bg-gray-50 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <!-- Botón Anterior -->
                        <button type="button" wire:click="previousStep"
                            class="inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 {{ $currentStep == 1 ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-sm' }}"
                            {{ $currentStep == 1 ? 'disabled' : '' }}>
                            <svg class="mr-2 h-4 w-4 transition-transform duration-200 {{ $currentStep == 1 ? '' : 'group-hover:-translate-x-1' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Anterior
                        </button>

                        <!-- Botón Siguiente/Finalizar -->
                        <button type="submit" wire:loading.attr="disabled"
                            class="relative inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 disabled:opacity-80 disabled:cursor-not-allowed min-w-[120px] overflow-hidden">

                            <!-- Contenido normal del botón -->
                            <span wire:loading.remove class="flex items-center transition-opacity duration-200">
                                @if($currentStep == $totalSteps)
                                    Finalizar
                                    <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    Siguiente
                                    <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                @endif
                            </span>

                            <!-- Estado de carga -->
                            <span wire:loading class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                @if($currentStep == $totalSteps)
                                    Finalizando...
                                @else
                                    Guardando...
                                @endif
                            </span>

                            <!-- Overlay sutil durante carga -->
                            <div wire:loading class="absolute inset-0 bg-indigo-700 opacity-20 rounded-lg"></div>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('redirect-after-delay', (url) => {
            setTimeout(() => {
                window.location.href = url;
            }, 2000);
        });

        // Listener para SweetAlert de configuración completada
        Livewire.on('show-completion-alert', (data) => {
            console.log('🎉 SweetAlert event received:', data);
            console.log('🔍 Swal available:', typeof Swal !== 'undefined');

            if (typeof Swal === 'undefined') {
                console.error('❌ SweetAlert2 no está disponible');
                // Fallback: mostrar alert nativo y redirigir
                alert('¡Configuración completada exitosamente!\n\nSerás redirigido al panel principal.');
                window.location.href = data[0].redirectTo;
                return;
            }

            Swal.fire({
                title: data[0].title,
                text: data[0].message,
                icon: 'success',
                showCancelButton: false,
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'Ir al Panel',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = data[0].redirectTo;
                }
            });
        });
    });
</script>