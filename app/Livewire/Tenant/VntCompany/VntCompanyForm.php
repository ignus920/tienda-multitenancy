<?php

namespace App\Livewire\Tenant\VntCompany;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Livewire\Tenant\VntCompany\Services\CompanyService;
use App\Livewire\Tenant\VntCompany\Services\WarehouseService;
use App\Livewire\Tenant\VntCompany\Services\CompanyQueryService;
use App\Livewire\Tenant\VntCompany\Services\CompanyValidationService;
use App\Livewire\Tenant\VntCompany\Services\ExportService;

class VntCompanyForm extends Component
{
    use WithPagination;

    // Services
    protected $companyService;
    protected $warehouseService;
    protected $queryService;
    protected $validationService;
    protected $exportService;
    protected $listeners = [
        'type-identification-changed' => 'updateTypeIdentification',
        'regime-changed' => 'updateRegime',
        'fiscal-responsibility-changed' => 'updateFiscalResponsibility',
        'city-changed' => 'updateWarehouseCity',
        'position-changed' => 'updatePosition'
    ];

    public $search = '';
    public $showModal = false;
    public $editingId = null;
    public $perPage = 10;
    public $sortField = 'id';
    public $sortDirection = 'desc';

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

    public function boot(
        CompanyService $companyService,
        WarehouseService $warehouseService,
        CompanyQueryService $queryService,
        CompanyValidationService $validationService,
        ExportService $exportService
    ) {
        $this->companyService = $companyService;
        $this->warehouseService = $warehouseService;
        $this->queryService = $queryService;
        $this->validationService = $validationService;
        $this->exportService = $exportService;
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
        $this->sortDirection
     ); 
   }

   public function render()
   {
     return view('livewire.tenant.vnt-company.vnt-company-form', [
        'items' => $this->items // Se cachea automáticamente entre renders
    ]);
   }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }



    public function edit($id)
    {
        $company = $this->companyService->getCompanyWithWarehouses($id);
        
        $this->editingId = $id;
        $this->typeIdentificationId = $company->typeIdentificationId;
        $this->identification = $company->identification;
        $this->firstName = $company->firstName;
        $this->secondName = $company->secondName;
        $this->lastName = $company->lastName;
        $this->businessName = $company->businessName;
        $this->billingEmail = $company->billingEmail;
        $this->typePerson = $company->typePerson;
        $this->regimeId = $company->regimeId;
        $this->fiscalResponsabilityId = $company->fiscalResponsabilityId;
        $this->code_ciiu = $company->code_ciiu;
        $this->checkDigit = $company->checkDigit;
        $this->verification_digit = $company->checkDigit; // Cargar el DV desde checkDigit
        $this->status = $company->status ?? 1;
        
        // Cargar sucursales usando el service
        $this->warehouses = $this->warehouseService->prepareWarehousesForForm($company);
        
        // Si no hay sucursales, inicializar con una por defecto
        if (empty($this->warehouses)) {
            $this->initializeDefaultWarehouse();
        } else {
            // Evaluar permisos para la empresa existente
            $this->evaluateWarehousePermissions();
        }
        
        $this->showModal = true;
    }

    public function save()
    {
        // Establecer typePerson automáticamente si no es NIT antes de validar
        if ($this->typeIdentificationId && (int) $this->typeIdentificationId !== 2 && empty($this->typePerson)) {
            $this->typePerson = 'Natural';
        }
        
        // Convertir strings vacíos a null solo para campos opcionales en Persona Natural
        if ($this->typePerson === 'Natural') {
            $this->regimeId = $this->regimeId === '' ? null : $this->regimeId;
            $this->fiscalResponsabilityId = $this->fiscalResponsabilityId === '' ? null : $this->fiscalResponsabilityId;
        }
        
        // warehouseCityId y positionId ya NO se convierten a null (son requeridos)
        
        // Validar usando las reglas del servicio
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            $errorMessage = 'Por favor corrija los siguientes errores:<br>' . implode('<br>', $errors);
            
            session()->flash('error', $errorMessage);
            $this->dispatch('show-validation-errors', ['errors' => $errors]);
            return;
        }
        
        $data = $this->getFormData();
        
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
            'name' => $this->warehouseName,
            'address' => $this->warehouseAddress,
            'postcode' => $this->warehousePostcode,
            'cityId' => $this->warehouseCityId,
            'main' => true, // Siempre es la sucursal principal
        ]];
        
        try {
            if ($this->editingId) {
                $this->companyService->update($this->editingId, $data, $warehouses);
                session()->flash('message', 'Registro actualizado exitosamente.');
            } else {
                $this->companyService->create($data, $warehouses);
                session()->flash('message', 'Registro creado exitosamente.');
            }

            $this->resetForm();
            $this->showModal = false;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
            return;
        }
    }

    public function delete($id)
    {
        try {
            $this->companyService->delete($id);
            session()->flash('message', 'Registro eliminado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
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
        
        // Reset warehouse fields e inicializar con una sucursal por defecto
        $this->warehouses = [];
        $this->initializeDefaultWarehouse();
        $this->warehouseName = '';
        $this->warehouseAddress = '';
        $this->warehousePostcode = '';
        $this->warehouseCityId = '';
        $this->warehouseIsMain = false;
        $this->canAddMoreWarehouses = false;

        $this->resetErrorBag();
    }

    public function updateTypeIdentification($typeIdentificationId)
    {
        $this->typeIdentificationId = $typeIdentificationId;
        
        // Lógica de negocio: establecer tipo de persona según tipo de identificación
        if ((int) $typeIdentificationId === 2) {
            // NIT: Permitir elegir entre Natural y Jurídica (no establecer automáticamente)
            // El usuario debe elegir manualmente
        } else {
            // Cualquier otro tipo de identificación: Automáticamente Persona Natural
            $this->typePerson = 'Natural';
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
        
        // También actualizar en el array de warehouses si existe (para compatibilidad)
        if (isset($this->warehouses[$index])) {
            $this->warehouses[$index]['cityId'] = (int) $cityId;
        }
        
        // Log para debugging
        Log::info('City updated', [
            'warehouseCityId' => $this->warehouseCityId,
            'index' => $index
        ]);
    }

    public function updatePosition($positionId)
    {
        $this->positionId = $positionId;
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
     */
    public function updatedTypeIdentificationId(): void
    {
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
     * Obtener datos del formulario para enviar al service
     */
    private function getFormData(): array
    {
        // Si es NIT, usar verification_digit como checkDigit
        $checkDigit = ((int) $this->typeIdentificationId === 2) 
            ? $this->verification_digit 
            : $this->checkDigit;
        
        return [
            'typeIdentificationId' => $this->typeIdentificationId,
            'identification' => $this->identification,
            'firstName' => $this->firstName,
            'secondName' => $this->secondName,
            'lastName' => $this->lastName,
            'secondLastName' => $this->secondLastName,
            'businessName' => $this->businessName,
            'billingEmail' => $this->billingEmail,
            'typePerson' => $this->typePerson,
            'checkDigit' => $checkDigit,
            'code_ciiu' => $this->code_ciiu,
            'regimeId' => $this->regimeId,
            'fiscalResponsabilityId' => $this->fiscalResponsabilityId,
            'status' => $this->status,
            'business_phone' => $this->business_phone,
            'personal_phone' => $this->personal_phone,
            'positionId' => $this->positionId,
        ];
    }
}
