<?php

namespace App\Livewire\Tenant\VntCompany\Services;

use App\Models\Tenant\VntCompany;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

class CompanyService
{
    protected $warehouseService;
    protected $contactService;

    public function __construct(WarehouseService $warehouseService, ContactService $contactService)
    {
        $this->warehouseService = $warehouseService;
        $this->contactService = $contactService;
    }

    /**
     * Crear una nueva empresa
     */
    public function create(array $data, array $warehouses = []): VntCompany
    {
        $this->ensureTenantConnection();
        
        // dd($data, $warehouses);


        $companyData = $this->prepareCompanyData($data);
        $company = VntCompany::create($companyData);
        
        // Crear almacenes
        $this->warehouseService->createWarehouses($company, $warehouses);
        
        // Preparar datos adicionales para el contacto (teléfonos y posición)
        $contactAdditionalData = [
            'business_phone' => $data['business_phone'] ?? null,
            'personal_phone' => $data['personal_phone'] ?? null,
            'positionId' => $data['positionId'] ?? 1,
        ];
        
        // Crear contacto básico automáticamente usando los datos de la empresa
        $this->contactService->createContactsForCompany($company, $contactAdditionalData);
        
        return $company;
    }

    /**
     * Actualizar una empresa existente
     */
    public function update(int $id, array $data, array $warehouses = []): VntCompany
    {
        $this->ensureTenantConnection();
        
        $company = VntCompany::findOrFail($id);
        $companyData = $this->prepareCompanyData($data);
        
        $company->update($companyData);
        
        // Actualizar almacenes
        if (!empty($warehouses)) {
            $this->warehouseService->updateWarehouses($company, $warehouses);
        }
        
        // Preparar datos adicionales para el contacto (teléfonos y posición)
        $contactAdditionalData = [
            'business_phone' => $data['business_phone'] ?? null,
            'personal_phone' => $data['personal_phone'] ?? null,
            'positionId' => $data['positionId'] ?? 1,
        ];
        
        // Actualizar contacto básico con los nuevos datos de la empresa
        $this->contactService->updateContactForCompany($company, $contactAdditionalData);
        
        return $company;
    }

    /**
     * Eliminar una empresa
     */
    public function delete(int $id): bool
    {
        $this->ensureTenantConnection();
        
        $company = VntCompany::findOrFail($id);
        return $company->delete();
    }

    /**
     * Obtener una empresa con sus sucursales
     */
    public function getCompanyWithWarehouses(int $id): VntCompany
    {
        $this->ensureTenantConnection();
        
        return VntCompany::with('warehouses')->findOrFail($id);
    }

    /**
     * Obtener una empresa con todos sus datos relacionados
     */
    public function getCompanyWithAllRelations(int $id): VntCompany
    {
        $this->ensureTenantConnection();
        
        return VntCompany::with([
            'warehouses',
            'contacts.warehouse',
            'contacts.position'
        ])->findOrFail($id);
    }

    /**
     * Obtener contactos de una empresa
     */
    public function getCompanyContacts(int $companyId): \Illuminate\Database\Eloquent\Collection
    {
        $this->ensureTenantConnection();
        
        $company = VntCompany::findOrFail($companyId);
        return $this->contactService->getCompanyContacts($company);
    }

    /**
     * Preparar datos de la empresa aplicando reglas de negocio
     */
    private function prepareCompanyData(array $data): array
    {
        $preparedData = [
            'typeIdentificationId' => $data['typeIdentificationId'],
            'identification' => $data['identification'],
            'businessName' => $data['businessName'] ?? null,
            'billingEmail' => $data['billingEmail'] ?? null,
            'typePerson' => $data['typePerson'], // cambia dinamicamentes segun la persona
            'business_phone' => $data['business_phone'] ?? null,
            'personal_phone' => $data['business_phone'] ?? null,
            'status' => $data['status'] ?? 1,
        ];

        // Aplicar reglas específicas según el tipo de identificación
        $typeIdentificationId = (int) $data['typeIdentificationId'];
         $typePerson = $data['typePerson']; // para hacer la segunda validación de lo que se va a enviar

         if($typePerson == 'Juridica'){
           
         }else{
          
         }
        if ($typeIdentificationId != 2 && $typePerson == 'Natural') {
            
            // Persona natural - valores por defecto
              $preparedData['typePerson'] = 'PERSON_ENTITY';
              $preparedData['firstName'] = $data['firstName'] ?? null;
              $preparedData['secondName'] = $data['secondName'] ?? null;
              $preparedData['lastName'] = $data['lastName'] ?? null;
              $preparedData['secondLastName'] = $data['secondLastName'] ?? null;
              $preparedData['checkDigit'] = null;
              $preparedData['code_ciiu'] = '0';
              $preparedData['regimeId'] = 2;
              $preparedData['fiscalResponsabilityId'] = 1;

        } else if($typeIdentificationId == 2 && $typePerson == 'Natural'){

             // Persona con nit y valores naturales
              $preparedData['typePerson'] = 'LEGAL_ENTITY';
              $preparedData['firstName'] = $data['firstName'] ?? null;
              $preparedData['secondName'] = $data['secondName'] ?? null;
              $preparedData['lastName'] = $data['lastName'] ?? null;
              $preparedData['secondLastName'] = $data['secondLastName'] ?? null;
              $preparedData['checkDigit'] = null;
              $preparedData['code_ciiu'] = '0';
              $preparedData['regimeId'] = 2;
              $preparedData['fiscalResponsabilityId'] = 1;

        }else{
            // Persona jurídica - usar valores proporcionados
            $preparedData['typePerson'] = 'LEGAL_ENTITY';
            $preparedData['businessName'] = $data['businessName'] ?? null;
            $preparedData['firstName'] = $data['businessName'] ?? null;
            $preparedData['checkDigit'] = $data['checkDigit'] ?? null;
            $preparedData['code_ciiu'] = $data['code_ciiu'] ?? null;
            $preparedData['regimeId'] = $data['regimeId'] ?? null;
            $preparedData['fiscalResponsabilityId'] = $data['fiscalResponsabilityId'] ?? null;
        }

        //dd($preparedData); // debugear
        return $preparedData;
    }

    /**
     * Asegurar conexión tenant
     */
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

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}