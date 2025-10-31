<?php

use App\Models\Central\VntCompany;
use App\Models\Central\VntContact;
use App\Models\Central\VntWarehouse;
use App\Models\Central\CnfTypeIdentification;
use App\Models\Central\CnfRegime;
use App\Models\Central\CnfFiscalResponsability;
use App\Models\Central\CnfCity;
use App\Services\Company\CompanyDataValidator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Volt\Component;

new class extends Component
{
    // Control de pasos
    public int $currentStep = 1;
    public int $totalSteps = 2;

    // Datos de la empresa (campos existentes)
    public string $identification = '';
    public string $verification_digit = '';
    public string $typePerson = '';
    public string $code_ciiu = '';
    public int $typeIdentificationId = 0;
    public int $regimeId = 0;
    public int $fiscalResponsabilityId = 0;

    // Campos para persona natural
    public string $firstName = '';
    public string $lastName = '';

    // Campo para persona jurídica
    public string $businessName = '';

    // Datos del contacto (campos existentes)
    public int $positionId = 0;
    public int $warehouseId = 0;

    // Datos del warehouse (campos existentes)
    public string $postcode = '';
    public int $cityId = 0;
    public int $termId = 1;

    // Campos para sucursales
    public bool $hasMultipleBranches = false;
    public string $branchName = '';
    public string $branchType = 'fija';
    public string $address = '';
    public string $city = '';
    public string $billingFormat = '';
    public bool $isCredit = false;
    public int $creditLimit = 0;
    public bool $hasPriceList = false;
    public int $apiDataId = 0;
    public int $countriId = 48; // País por defecto Colombia

    // Colecciones para selects
    public $cities = [];

    // Estado
    public string $successMessage = '';

    // Datos existentes
    public ?VntCompany $company = null;
    public ?VntContact $contact = null;
    public ?VntWarehouse $warehouse = null;

    // Datos para selects
    public $typeIdentifications = [];
    public $regimes = [];
    public $fiscalResponsabilities = [];

    public function mount()
    {
        $this->loadSelectData();
        $this->loadExistingData();
        $this->determineCurrentStep();
    }

    public function layout()
    {
        return 'layouts.guest';
    }

    protected function loadSelectData()
    {
        // Cargar datos para los selects desde la base de datos central
        $this->typeIdentifications = CnfTypeIdentification::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'acronym']);

        $this->regimes = CnfRegime::where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        $this->fiscalResponsabilities = CnfFiscalResponsability::orderBy('description')
            ->get(['id', 'description']);

        // Cargar ciudades filtradas por país (Colombia = 48)
        $this->cities = CnfCity::where('country_id', $this->countriId)
            ->orderBy('name')
            ->get(['id', 'name', 'state_id']);
    }

    protected function loadExistingData()
    {
        $validator = app(CompanyDataValidator::class);
        $user = Auth::user();

        $this->company = $validator->getUserCompany($user);
        $this->contact = VntContact::where('email', $user->email)->first();
        $this->warehouse = $this->company ? VntWarehouse::where('companyId', $this->company->id)->first() : null;

        // Cargar datos existentes en los campos
        if ($this->company) {
            $this->identification = $this->company->identification ?? '';
            $this->verification_digit = $this->company->verification_digit ?? '';
            $this->typePerson = $this->company->typePerson ?? '';
            $this->code_ciiu = $this->company->code_ciiu ?? '';
            $this->typeIdentificationId = $this->company->typeIdentificationId ?? 0;
            $this->regimeId = $this->company->regimeId ?? 0;
            $this->fiscalResponsabilityId = $this->company->fiscalResponsabilityId ?? 0;

            // Cargar campos de persona
            $this->firstName = $this->company->firstName ?? '';
            $this->lastName = $this->company->lastName ?? '';
            $this->businessName = $this->company->businessName ?? '';
        }

        if ($this->contact) {
            $this->positionId = $this->contact->positionId ?? 0;
            $this->warehouseId = $this->contact->warehouseId ?? 0;
        }

        if ($this->warehouse) {
            $this->postcode = $this->warehouse->postcode ?? '';
            $this->cityId = $this->warehouse->cityId ?? 0;
            $this->termId = $this->warehouse->termId ?? 1;

            // Cargar datos de sucursal
            $this->branchName = $this->warehouse->name ?? '';
            $this->branchType = $this->warehouse->branch_type ?? 'fija';
            $this->address = $this->warehouse->address ?? '';
            $this->city = $this->warehouse->city ?? '';
            $this->billingFormat = $this->warehouse->billingFormat ?? '';
            $this->isCredit = (bool) ($this->warehouse->is_credit ?? false);
            $this->creditLimit = $this->warehouse->creditLimit ?? 0;
            $this->hasPriceList = !($this->warehouse->pric_list ?? false);
            $this->apiDataId = $this->warehouse->integrationDataId ?? 0;
            $this->countriId = $this->warehouse->countri_id ?? 48;

            // Determinar si tiene múltiples sucursales (lógica simple por ahora)
            $this->hasMultipleBranches = $this->branchName !== 'Principal';
        }
    }

    protected function determineCurrentStep()
    {
        // Verificar si los datos básicos de la empresa están completos (Paso 1)
        if (!$this->company ||
            empty($this->identification) ||
            empty($this->typePerson) ||
            empty($this->code_ciiu) ||
            $this->typeIdentificationId == 0 ||
            $this->regimeId == 0 ||
            $this->fiscalResponsabilityId == 0) {
            $this->currentStep = 1;
            return;
        }

        // Verificar si los datos de ubicación están completos (Paso 2)
        if (empty($this->address) ||
            $this->cityId == 0 ||
            empty($this->postcode)) {
            $this->currentStep = 2;
            return;
        }

        // Todos los datos están completos, redirigir al dashboard
        $this->redirect(route('tenant.select'));
    }

    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function validateCurrentStep()
    {
        switch ($this->currentStep) {
            case 1:
                $this->validateStep1();
                break;
            case 2:
                $this->validateStep2();
                break;
        }
    }

    protected function validateStep1()
    {
        $rules = [
            'typeIdentificationId' => ['required', 'integer', 'min:1'],
            'identification' => ['required', 'string', 'max:15'],
            'typePerson' => ['required', 'string'],
            'code_ciiu' => ['required', 'string'],
            'regimeId' => ['required', 'integer', 'min:1'],
            'fiscalResponsabilityId' => ['required', 'integer', 'min:1'],
        ];

        // Si es NIT (id=2), también validar el dígito de verificación
        if ($this->typeIdentificationId == 2) {
            $rules['verification_digit'] = ['required', 'string', 'max:1'];
        }

        // Validar campos según tipo de persona
        if ($this->typePerson == 'Natural') {
            $rules['firstName'] = ['required', 'string', 'max:100'];
            $rules['lastName'] = ['required', 'string', 'max:100'];
        } elseif ($this->typePerson == 'Juridica') {
            $rules['businessName'] = ['required', 'string', 'max:255'];
        }

        $this->validate($rules);
        $this->saveStep1();
    }

    protected function validateStep2()
    {
        $rules = [
            'hasMultipleBranches' => ['required', 'boolean'],
            'address' => ['required', 'string', 'max:255'],
            'cityId' => ['required', 'integer', 'min:1'],
            'postcode' => ['required', 'string', 'max:10'],
        ];

        // Si tiene múltiples sucursales, validar nombre de sucursal
        if ($this->hasMultipleBranches) {
            $rules['branchName'] = ['required', 'string', 'max:255'];
        } else {
            // Si no tiene múltiples sucursales, automáticamente asignar "Principal"
            $this->branchName = 'Principal';
        }

        $this->validate($rules);
        $this->saveStep2();
    }


    protected function saveStep1()
    {
        if ($this->company) {
            $updateData = [
                'identification' => $this->identification,
                'typePerson' => $this->typePerson,
                'code_ciiu' => $this->code_ciiu,
                'typeIdentificationId' => $this->typeIdentificationId,
                'regimeId' => $this->regimeId,
                'fiscalResponsabilityId' => $this->fiscalResponsabilityId,
            ];

            // Solo actualizar verification_digit si es NIT
            if ($this->typeIdentificationId == 2) {
                $updateData['verification_digit'] = $this->verification_digit;
            } else {
                $updateData['verification_digit'] = null;
            }

            // Guardar campos según tipo de persona
            if ($this->typePerson == 'Natural') {
                $updateData['firstName'] = $this->firstName;
                $updateData['lastName'] = $this->lastName;
                $updateData['businessName'] = null; // Limpiar razón social
            } elseif ($this->typePerson == 'Juridica') {
                $updateData['businessName'] = $this->businessName;
                $updateData['firstName'] = null; // Limpiar nombre
                $updateData['lastName'] = null; // Limpiar apellido
            }

            $this->company->update($updateData);

            Log::info('✅ Datos de empresa actualizados', ['company_id' => $this->company->id]);
        }
    }

    protected function saveStep2()
    {
        if ($this->warehouse) {
            $updateData = [
                'name' => $this->branchName,
                'address' => $this->address,
                'postcode' => $this->postcode,
                'countri_id' => $this->countriId,
                'cityId' => $this->cityId,
                'billingFormat' => 16, // Valor por defecto
                'is_credit' => 0, // Valor por defecto
                'termId' => 1, // Valor por defecto
                'creditLimit' => '0', // Valor por defecto
                'status' => 1,
                'main' => 1, // Principal
            ];

            $this->warehouse->update($updateData);

            Log::info('✅ Datos de warehouse/sucursal actualizados', ['warehouse_id' => $this->warehouse->id]);
        }
    }


    public function finish()
    {
        $this->validateCurrentStep();

        // Ya completamos todos los pasos necesarios, redirigir al dashboard
        $this->successMessage = '¡Configuración completada exitosamente!';

        Log::info('🎉 Configuración de empresa completada', ['user_id' => Auth::user()->id]);

        // Redirigir al dashboard inmediatamente
        $this->redirect(route('tenant.select'));
    }

    public function getProgressPercentage(): int
    {
        // Calcular progreso basado en los 2 pasos
        $step1Complete = !empty($this->identification) &&
                        !empty($this->typePerson) &&
                        !empty($this->code_ciiu) &&
                        $this->typeIdentificationId > 0 &&
                        $this->regimeId > 0 &&
                        $this->fiscalResponsabilityId > 0;

        $step2Complete = !empty($this->address) &&
                        $this->cityId > 0 &&
                        !empty($this->postcode);

        if ($step1Complete && $step2Complete) {
            return 100;
        } elseif ($step1Complete) {
            return 50;
        } else {
            return 25;
        }
    }
}; ?>

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
                                <div>
                                    <label for="typeIdentificationId" class="block text-sm font-medium text-gray-700">Tipo Identificación *</label>
                                    <select wire:model.live="typeIdentificationId" id="typeIdentificationId"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="0">Seleccionar tipo</option>
                                        @foreach($typeIdentifications as $type)
                                            <option value="{{ $type->id }}">{{ $type->acronym }} - {{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('typeIdentificationId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- NIT/Identificación con campo DV condicional -->
                                @if($typeIdentificationId > 0)
                                    @if($typeIdentificationId == 2)
                                        <!-- NIT con DV -->
                                        <div class="grid grid-cols-3 gap-4">
                                            <div class="col-span-2">
                                                <label for="identification" class="block text-sm font-medium text-gray-700">NIT *</label>
                                                <input wire:model="identification" type="text" id="identification" maxlength="15"
                                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                    placeholder="123456789">
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
                                            <input wire:model="identification" type="text" id="identification" maxlength="15"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
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
                                    <div>
                                        <label for="regimeId" class="block text-sm font-medium text-gray-700">Régimen *</label>
                                        <select wire:model="regimeId" id="regimeId"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="0">Seleccionar régimen</option>
                                            @foreach($regimes as $regime)
                                                <option value="{{ $regime->id }}">{{ $regime->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('regimeId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="fiscalResponsabilityId" class="block text-sm font-medium text-gray-700">Responsabilidad Fiscal *</label>
                                        <select wire:model="fiscalResponsabilityId" id="fiscalResponsabilityId"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="0">Seleccionar responsabilidad</option>
                                            @foreach($fiscalResponsabilities as $responsibility)
                                                <option value="{{ $responsibility->id }}">{{ $responsibility->description }}</option>
                                            @endforeach
                                        </select>
                                        @error('fiscalResponsabilityId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
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
                                        <div>
                                            <label for="cityId" class="block text-sm font-medium text-gray-700">Ciudad *</label>
                                            <select wire:model="cityId" id="cityId"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="0">Seleccionar ciudad</option>
                                                @foreach($cities as $city)
                                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('cityId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

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
    });
</script>