<?php

namespace App\Livewire\Tenant\VntCompany\Services;

class CompanyValidationService
{
    /**
     * Obtener reglas de validación dinámicas
     */
    public function getValidationRules(
        string $typePerson = '', 
        ?int $editingId = null, 
        ?int $typeIdentificationId = null,
        bool $includeWarehouseAndContact = false
    ): array {
        $baseRules = $this->getBaseRules($editingId, $typeIdentificationId);
        
        // Aplicar reglas según tipo de persona
        $rules = match ($typePerson) {
            'Juridica' => $this->getJuridicalPersonRules($baseRules),
            'Natural' => $this->getNaturalPersonRules($baseRules),
            default => $baseRules,
        };
        
        // Si se solicita, incluir reglas de warehouse y contacto
        if ($includeWarehouseAndContact) {
            $rules = $this->addWarehouseAndContactRules($rules);
        }
        
        return $rules;
    }

    /**
     * Obtener mensajes de validación personalizados
     */
    public function getValidationMessages(): array
    {
        return [
            // Campos base
            'identification.required' => 'El número de identificación es obligatorio.',
            'identification.unique' => 'Este número de identificación ya está registrado.',
            'typePerson.required' => 'Debe seleccionar el tipo de persona.',
            'typeIdentificationId.required' => 'Debe seleccionar el tipo de identificación.',
            'typeIdentificationId.exists' => 'El tipo de identificación seleccionado no es válido.',
            'billingEmail.email' => 'El email de facturación debe tener un formato válido.',
            
            // Persona jurídica
            'businessName.required' => 'La razón social es obligatoria para personas jurídicas.',
            'regimeId.required' => 'El régimen es obligatorio para personas jurídicas.',
            'fiscalResponsabilityId.required' => 'La responsabilidad fiscal es obligatoria para personas jurídicas.',
            
            // Persona natural
            'firstName.required' => 'El primer nombre es obligatorio para personas naturales.',
            'lastName.required' => 'El apellido es obligatorio para personas naturales.',
            
            // Warehouse (campos individuales)
            'warehouseName.required' => 'El nombre de la sucursal es obligatorio.',
            'warehouseAddress.required' => 'La dirección de la sucursal es obligatoria.',
            'warehousePostcode.max' => 'El código postal no puede tener más de 10 caracteres.',
            
            // Contacto
            'business_phone.max' => 'El teléfono empresarial no puede tener más de 100 caracteres.',
            'personal_phone.max' => 'El teléfono personal no puede tener más de 100 caracteres.',
            'positionId.exists' => 'La posición seleccionada no es válida.',
        ];
    }

    /**
     * Obtener atributos personalizados para validación
     */
    public function getValidationAttributes(): array
    {
        return [
            // Campos base
            'identification' => 'número de identificación',
            'typePerson' => 'tipo de persona',
            'typeIdentificationId' => 'tipo de identificación',
            'billingEmail' => 'email de facturación',
            'checkDigit' => 'dígito de verificación',
            'code_ciiu' => 'código CIIU',
            'status' => 'estado',
            
            // Persona jurídica
            'businessName' => 'razón social',
            'regimeId' => 'régimen',
            'fiscalResponsabilityId' => 'responsabilidad fiscal',
            
            // Persona natural
            'firstName' => 'primer nombre',
            'lastName' => 'apellido',
            'secondName' => 'segundo nombre',
            'secondLastName' => 'segundo apellido',
            
            // Warehouse (campos individuales)
            'warehouseName' => 'nombre de sucursal',
            'warehouseAddress' => 'dirección de sucursal',
            'warehousePostcode' => 'código postal',
            'warehouseCityId' => 'ciudad',
            
            // Contacto
            'business_phone' => 'teléfono empresarial',
            'personal_phone' => 'teléfono personal',
            'positionId' => 'posición',
        ];
    }

    /**
     * Validar datos específicos de persona natural
     */
    public function validateNaturalPerson(array $data): array
    {
        $errors = [];

        if (empty($data['firstName'])) {
            $errors['firstName'] = 'El primer nombre es obligatorio para personas naturales.';
        }

        if (empty($data['lastName'])) {
            $errors['lastName'] = 'El apellido es obligatorio para personas naturales.';
        }

        return $errors;
    }

    /**
     * Validar datos específicos de persona jurídica
     */
    public function validateJuridicalPerson(array $data): array
    {
        $errors = [];

        if (empty($data['businessName'])) {
            $errors['businessName'] = 'La razón social es obligatoria para personas jurídicas.';
        }

        return $errors;
    }

    /**
     * Validar sucursales
     */
    public function validateWarehouses(array $warehouses): array
    {
        $errors = [];
        $hasMainWarehouse = false;

        foreach ($warehouses as $index => $warehouse) {
            if (empty($warehouse['name'])) {
                $errors["warehouses.{$index}.name"] = 'El nombre de la sucursal es obligatorio.';
            }

            if (empty($warehouse['address'])) {
                $errors["warehouses.{$index}.address"] = 'La dirección de la sucursal es obligatoria.';
            }

            if (!empty($warehouse['postcode']) && strlen($warehouse['postcode']) > 10) {
                $errors["warehouses.{$index}.postcode"] = 'El código postal no puede tener más de 10 caracteres.';
            }

            if ($warehouse['main']) {
                if ($hasMainWarehouse) {
                    $errors["warehouses.{$index}.main"] = 'Solo puede haber una sucursal principal.';
                }
                $hasMainWarehouse = true;
            }
        }

        if (!empty($warehouses) && !$hasMainWarehouse) {
            $errors['warehouses'] = 'Debe designar al menos una sucursal como principal.';
        }

        return $errors;
    }

    /**
     * Obtener reglas base de validación
     */
    private function getBaseRules(?int $editingId = null, ?int $typeIdentificationId = null): array
    {
        $identificationRule = 'required|string|max:15|unique:vnt_companies,identification';
        
        if ($editingId) {
            $identificationRule .= ',' . $editingId;
        }

        // Determinar si typePerson es requerido basado en el tipo de identificación
        $typePersonRule = 'required|string|in:Natural,Juridica';
        
        // Si NO es NIT (typeIdentificationId != 2), typePerson puede ser nullable porque se establece automáticamente
        if ($typeIdentificationId && (int) $typeIdentificationId !== 2) {
            $typePersonRule = 'nullable|string|in:Natural,Juridica';
        }

        return [
            'identification' => $identificationRule,
            'typePerson' => $typePersonRule,
            'typeIdentificationId' => 'required|integer|exists:central.cnf_type_identifications,id',
            'status' => 'nullable|integer|in:0,1',
            'billingEmail' => 'nullable|email|max:255',
            'checkDigit' => 'nullable|integer|max:99',
            'integrationDataId' => 'nullable|integer',
            'code_ciiu' => 'nullable|string|max:255',
            'verification_digit' => 'nullable|string|max:1',
            'warehouses' => 'array',
            'warehouses.*.name' => 'required|string|max:255',
            'warehouses.*.address' => 'required|string|max:255',
            'warehouses.*.postcode' => 'nullable|string|max:10',
            'warehouses.*.cityId' => 'nullable|integer',
            'warehouses.*.main' => 'boolean',
        ];
    }

    /**
     * Obtener reglas para persona jurídica
     */
    private function getJuridicalPersonRules(array $baseRules): array
    {

        
        return array_merge($baseRules, [
            'businessName' => 'required|string|max:255',
            'regimeId' => 'required|integer',
             'fiscalResponsabilityId' => 'required|integer',
        ]);
    }

    /**
     * Obtener reglas para persona natural
     */
    private function getNaturalPersonRules(array $baseRules): array
    {
        return array_merge($baseRules, [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'secondName' => 'nullable|string|max:255',
            'secondLastName' => 'nullable|string|max:255',
            'fiscalResponsabilityId' => 'nullable|integer',
            'regimeId' => 'nullable|integer',
        ]);
    }

    /**
     * Agregar reglas de warehouse y contacto
     */
    private function addWarehouseAndContactRules(array $rules): array
    {
        // Eliminar reglas obsoletas de warehouses.* (ya no usamos array)
        $warehouseKeys = ['warehouses', 'warehouses.*.name', 'warehouses.*.address', 'warehouses.*.postcode', 'warehouses.*.cityId', 'warehouses.*.main'];
        foreach ($warehouseKeys as $key) {
            unset($rules[$key]);
        }
        
        // Agregar reglas para campos individuales de warehouse
        return array_merge($rules, [
            'warehouseName' => 'required|string|max:255',
            'warehouseAddress' => 'required|string|max:255',
            'warehousePostcode' => 'nullable|string|max:10',
            'warehouseCityId' => 'nullable|integer',
            'business_phone' => 'nullable|string|max:100',
            'personal_phone' => 'nullable|string|max:100',
            'positionId' => 'nullable|integer|exists:cfg_positions,id',
        ]);
    }

    /**
     * Validar datos completos del formulario
     * Retorna array con 'valid' (bool) y 'errors' (array)
     */
    public function validateFormData(array $data, string $typePerson): array
    {
        $errors = [];

        // Validar según tipo de persona
        if ($typePerson === 'Natural') {
            $errors = array_merge($errors, $this->validateNaturalPerson($data));
        } elseif ($typePerson === 'Juridica') {
            $errors = array_merge($errors, $this->validateJuridicalPerson($data));
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}