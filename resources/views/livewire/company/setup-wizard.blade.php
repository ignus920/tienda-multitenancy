<?php

use App\Models\Central\VntCompany;
use App\Models\Central\VntContact;
use App\Models\Central\VntWarehouse;
use App\Models\Central\CnfCountry;
use App\Services\Company\CompanyDataValidator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Volt\Component;

new class extends Component
{
    // Control de pasos
    public int $currentStep = 1;
    public int $totalSteps = 3;

    // Datos de la empresa
    public string $nit = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $country = '';
    public string $zipCode = '';
    public string $phone = '';
    public string $website = '';
    public string $businessType = '';
    public string $legalRepresentative = '';

    // Datos del contacto
    public string $position = '';
    public string $department = '';
    public string $alternativePhone = '';
    public string $emergencyContact = '';
    public string $emergencyPhone = '';

    // Datos del warehouse
    public string $warehouseName = '';
    public string $warehouseAddress = '';
    public string $warehouseCity = '';
    public string $warehouseState = '';
    public string $warehouseCountry = '';
    public string $warehouseZipCode = '';
    public string $warehousePhone = '';
    public string $manager = '';
    public string $capacity = '';
    public string $operatingHours = '';

    // Colecciones
    public $countries = [];
    public $businessTypes = [];

    // Estado
    public bool $isLoading = false;
    public string $successMessage = '';

    // Datos existentes
    public ?VntCompany $company = null;
    public ?VntContact $contact = null;
    public ?VntWarehouse $warehouse = null;

    public function mount()
    {
        $this->countries = CnfCountry::where('status', 1)->get();
        $this->businessTypes = [
            'Retail' => 'Comercio al por menor',
            'Wholesale' => 'Comercio al por mayor',
            'Manufacturing' => 'Manufactura',
            'Services' => 'Servicios',
            'Technology' => 'Tecnología',
            'Food' => 'Alimentación',
            'Healthcare' => 'Salud',
            'Education' => 'Educación',
            'Other' => 'Otro'
        ];

        $this->loadExistingData();
        $this->determineCurrentStep();
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
            $this->nit = $this->company->nit ?? '';
            $this->address = $this->company->address ?? '';
            $this->city = $this->company->city ?? '';
            $this->state = $this->company->state ?? '';
            $this->country = $this->company->country ?? '';
            $this->zipCode = $this->company->zipCode ?? '';
            $this->phone = $this->company->phone ?? '';
            $this->website = $this->company->website ?? '';
            $this->businessType = $this->company->businessType ?? '';
            $this->legalRepresentative = $this->company->legalRepresentative ?? '';
        }

        if ($this->contact) {
            $this->position = $this->contact->position ?? '';
            $this->department = $this->contact->department ?? '';
            $this->alternativePhone = $this->contact->alternativePhone ?? '';
            $this->emergencyContact = $this->contact->emergencyContact ?? '';
            $this->emergencyPhone = $this->contact->emergencyPhone ?? '';
        }

        if ($this->warehouse) {
            $this->warehouseName = $this->warehouse->name ?? '';
            $this->warehouseAddress = $this->warehouse->address ?? '';
            $this->warehouseCity = $this->warehouse->city ?? '';
            $this->warehouseState = $this->warehouse->state ?? '';
            $this->warehouseCountry = $this->warehouse->country ?? '';
            $this->warehouseZipCode = $this->warehouse->zipCode ?? '';
            $this->warehousePhone = $this->warehouse->phone ?? '';
            $this->manager = $this->warehouse->manager ?? '';
            $this->capacity = $this->warehouse->capacity ?? '';
            $this->operatingHours = $this->warehouse->operatingHours ?? '';
        }
    }

    protected function determineCurrentStep()
    {
        $validator = app(CompanyDataValidator::class);
        $user = Auth::user();

        if (!$this->company || !$validator->isBasicCompanyDataComplete($this->company)) {
            $this->currentStep = 1;
        } elseif (!$validator->isContactDataComplete($user)) {
            $this->currentStep = 2;
        } elseif (!$validator->isWarehouseDataComplete($this->company)) {
            $this->currentStep = 3;
        } else {
            // Todos los datos están completos, redirigir al dashboard
            $this->redirect(route('tenant.select'));
        }
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
            case 3:
                $this->validateStep3();
                break;
        }
    }

    protected function validateStep1()
    {
        $this->validate([
            'nit' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'zipCode' => ['required', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:20'],
            'website' => ['nullable', 'url', 'max:255'],
            'businessType' => ['required', 'string'],
            'legalRepresentative' => ['required', 'string', 'max:255'],
        ]);

        $this->saveStep1();
    }

    protected function validateStep2()
    {
        $this->validate([
            'position' => ['required', 'string', 'max:100'],
            'department' => ['required', 'string', 'max:100'],
            'alternativePhone' => ['nullable', 'string', 'max:20'],
            'emergencyContact' => ['required', 'string', 'max:255'],
            'emergencyPhone' => ['required', 'string', 'max:20'],
        ]);

        $this->saveStep2();
    }

    protected function validateStep3()
    {
        $this->validate([
            'warehouseName' => ['required', 'string', 'max:255'],
            'warehouseAddress' => ['required', 'string', 'max:255'],
            'warehouseCity' => ['required', 'string', 'max:100'],
            'warehouseState' => ['required', 'string', 'max:100'],
            'warehouseCountry' => ['required', 'string', 'max:100'],
            'warehouseZipCode' => ['required', 'string', 'max:10'],
            'warehousePhone' => ['required', 'string', 'max:20'],
            'manager' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'string', 'max:100'],
            'operatingHours' => ['required', 'string', 'max:255'],
        ]);

        $this->saveStep3();
    }

    protected function saveStep1()
    {
        if ($this->company) {
            $this->company->update([
                'nit' => $this->nit,
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'zipCode' => $this->zipCode,
                'phone' => $this->phone,
                'website' => $this->website,
                'businessType' => $this->businessType,
                'legalRepresentative' => $this->legalRepresentative,
            ]);

            Log::info('✅ Datos de empresa actualizados', ['company_id' => $this->company->id]);
        }
    }

    protected function saveStep2()
    {
        if ($this->contact) {
            $this->contact->update([
                'position' => $this->position,
                'department' => $this->department,
                'alternativePhone' => $this->alternativePhone,
                'emergencyContact' => $this->emergencyContact,
                'emergencyPhone' => $this->emergencyPhone,
            ]);

            Log::info('✅ Datos de contacto actualizados', ['contact_id' => $this->contact->id]);
        }
    }

    protected function saveStep3()
    {
        if ($this->warehouse) {
            $this->warehouse->update([
                'name' => $this->warehouseName,
                'address' => $this->warehouseAddress,
                'city' => $this->warehouseCity,
                'state' => $this->warehouseState,
                'country' => $this->warehouseCountry,
                'zipCode' => $this->warehouseZipCode,
                'phone' => $this->warehousePhone,
                'manager' => $this->manager,
                'capacity' => $this->capacity,
                'operatingHours' => $this->operatingHours,
            ]);

            Log::info('✅ Datos de warehouse actualizados', ['warehouse_id' => $this->warehouse->id]);
        }
    }

    public function finish()
    {
        $this->validateCurrentStep();

        // Verificar que todos los datos estén completos
        $validator = app(CompanyDataValidator::class);
        $user = Auth::user();

        if ($validator->isCompanyDataComplete($user)) {
            $this->successMessage = '¡Configuración completada exitosamente!';

            Log::info('🎉 Configuración de empresa completada', ['user_id' => $user->id]);

            // Redirigir al dashboard después de 2 segundos
            $this->dispatch('redirect-after-delay', route('tenant.select'));
        } else {
            session()->flash('error', 'Aún hay datos pendientes por completar.');
        }
    }

    public function getProgressPercentage(): int
    {
        $validator = app(CompanyDataValidator::class);
        $completion = $validator->getCompletionPercentage(Auth::user());
        return $completion['overall'];
    }
}; ?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Configuración de Empresa</h1>
            <p class="mt-2 text-gray-600">Complete los datos de su empresa para continuar</p>
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
                    <!-- Step 1: Datos de la Empresa -->
                    @if($currentStep == 1)
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Datos de la Empresa</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- NIT -->
                                <div>
                                    <label for="nit" class="block text-sm font-medium text-gray-700">NIT *</label>
                                    <input wire:model="nit" type="text" id="nit"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('nit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono *</label>
                                    <input wire:model="phone" type="tel" id="phone"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Dirección -->
                                <div class="md:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700">Dirección *</label>
                                    <input wire:model="address" type="text" id="address"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Ciudad -->
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700">Ciudad *</label>
                                    <input wire:model="city" type="text" id="city"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label for="state" class="block text-sm font-medium text-gray-700">Estado/Provincia *</label>
                                    <input wire:model="state" type="text" id="state"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('state') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- País -->
                                <x-selects.country
                                    wire:model="country"
                                    name="country"
                                    placeholder="Seleccionar país"
                                    :error="$errors->first('country')"
                                />

                                <!-- Código Postal -->
                                <div>
                                    <label for="zipCode" class="block text-sm font-medium text-gray-700">Código Postal *</label>
                                    <input wire:model="zipCode" type="text" id="zipCode"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('zipCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Sitio Web -->
                                <div>
                                    <label for="website" class="block text-sm font-medium text-gray-700">Sitio Web</label>
                                    <input wire:model="website" type="url" id="website"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('website') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Tipo de Negocio -->
                                <div>
                                    <label for="businessType" class="block text-sm font-medium text-gray-700">Tipo de Negocio *</label>
                                    <select wire:model="businessType" id="businessType"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Seleccionar tipo</option>
                                        @foreach($businessTypes as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('businessType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Representante Legal -->
                                <div class="md:col-span-2">
                                    <label for="legalRepresentative" class="block text-sm font-medium text-gray-700">Representante Legal *</label>
                                    <input wire:model="legalRepresentative" type="text" id="legalRepresentative"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('legalRepresentative') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Step 2: Datos del Contacto -->
                    @if($currentStep == 2)
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Datos del Contacto Principal</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Cargo -->
                                <div>
                                    <label for="position" class="block text-sm font-medium text-gray-700">Cargo *</label>
                                    <input wire:model="position" type="text" id="position"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('position') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Departamento -->
                                <div>
                                    <label for="department" class="block text-sm font-medium text-gray-700">Departamento *</label>
                                    <input wire:model="department" type="text" id="department"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('department') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Teléfono Alternativo -->
                                <div>
                                    <label for="alternativePhone" class="block text-sm font-medium text-gray-700">Teléfono Alternativo</label>
                                    <input wire:model="alternativePhone" type="tel" id="alternativePhone"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('alternativePhone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Contacto de Emergencia -->
                                <div>
                                    <label for="emergencyContact" class="block text-sm font-medium text-gray-700">Contacto de Emergencia *</label>
                                    <input wire:model="emergencyContact" type="text" id="emergencyContact"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('emergencyContact') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Teléfono de Emergencia -->
                                <div class="md:col-span-2">
                                    <label for="emergencyPhone" class="block text-sm font-medium text-gray-700">Teléfono de Emergencia *</label>
                                    <input wire:model="emergencyPhone" type="tel" id="emergencyPhone"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('emergencyPhone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Step 3: Datos del Almacén -->
                    @if($currentStep == 3)
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">Datos del Almacén Principal</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nombre del Almacén -->
                                <div>
                                    <label for="warehouseName" class="block text-sm font-medium text-gray-700">Nombre del Almacén *</label>
                                    <input wire:model="warehouseName" type="text" id="warehouseName"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('warehouseName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Gerente -->
                                <div>
                                    <label for="manager" class="block text-sm font-medium text-gray-700">Gerente *</label>
                                    <input wire:model="manager" type="text" id="manager"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('manager') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Dirección del Almacén -->
                                <div class="md:col-span-2">
                                    <label for="warehouseAddress" class="block text-sm font-medium text-gray-700">Dirección *</label>
                                    <input wire:model="warehouseAddress" type="text" id="warehouseAddress"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('warehouseAddress') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Ciudad del Almacén -->
                                <div>
                                    <label for="warehouseCity" class="block text-sm font-medium text-gray-700">Ciudad *</label>
                                    <input wire:model="warehouseCity" type="text" id="warehouseCity"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('warehouseCity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Estado del Almacén -->
                                <div>
                                    <label for="warehouseState" class="block text-sm font-medium text-gray-700">Estado/Provincia *</label>
                                    <input wire:model="warehouseState" type="text" id="warehouseState"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('warehouseState') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- País del Almacén -->
                                <x-selects.country
                                    wire:model="warehouseCountry"
                                    name="warehouseCountry"
                                    id="warehouseCountry"
                                    label="País"
                                    placeholder="Seleccionar país"
                                    :error="$errors->first('warehouseCountry')"
                                />

                                <!-- Código Postal del Almacén -->
                                <div>
                                    <label for="warehouseZipCode" class="block text-sm font-medium text-gray-700">Código Postal *</label>
                                    <input wire:model="warehouseZipCode" type="text" id="warehouseZipCode"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('warehouseZipCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Teléfono del Almacén -->
                                <div>
                                    <label for="warehousePhone" class="block text-sm font-medium text-gray-700">Teléfono *</label>
                                    <input wire:model="warehousePhone" type="tel" id="warehousePhone"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @error('warehousePhone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Capacidad -->
                                <div>
                                    <label for="capacity" class="block text-sm font-medium text-gray-700">Capacidad *</label>
                                    <input wire:model="capacity" type="text" id="capacity"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="ej: 1000 m², 500 pallets">
                                    @error('capacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <!-- Horarios de Operación -->
                                <div class="md:col-span-2">
                                    <label for="operatingHours" class="block text-sm font-medium text-gray-700">Horarios de Operación *</label>
                                    <input wire:model="operatingHours" type="text" id="operatingHours"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="ej: Lun-Vie 8:00-18:00, Sáb 8:00-12:00">
                                    @error('operatingHours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-between">
                    <button type="button" wire:click="previousStep"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 {{ $currentStep == 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ $currentStep == 1 ? 'disabled' : '' }}>
                        <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Anterior
                    </button>

                    <button type="submit" wire:loading.attr="disabled"
                        class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span wire:loading.remove>
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
                        <span wire:loading>
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Procesando...
                        </span>
                    </button>
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