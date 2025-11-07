<?php

namespace App\Livewire\Tenant\VntCompany\Services;

use App\Models\Tenant\VntContacts;
use App\Models\Tenant\VntCompany;

class ContactService
{
    /**
     * Crear contacto básico para una empresa usando los datos existentes
     */
    public function createContactsForCompany(VntCompany $company, array $additionalData = []): void
    {
        // Obtener el almacén principal de la empresa
        $mainWarehouse = $company->mainWarehouse;
        
        if (!$mainWarehouse) {
            // Si no hay almacén principal, usar el primer almacén
            $mainWarehouse = $company->warehouses()->first();
        }

        if (!$mainWarehouse) {
            throw new \Exception('No se puede crear contacto: la empresa no tiene almacenes');
        }

        $contactData = [
            'warehouseId' => $mainWarehouse->id,
            'positionId' => $additionalData['positionId'] ?? 1, // Usar positionId del formulario o 1 por defecto
            'status' => 1,
            'email' => $company->billingEmail,
            'business_phone' => $additionalData['business_phone'] ?? null,
            'personal_phone' => $additionalData['personal_phone'] ?? null,
        ];

        // Lógica según el tipo de persona
        if ($company->typePerson === 'LEGAL_ENTITY') {
            // Persona jurídica - solo firstName con businessName
            $contactData['firstName'] = $company->businessName;
        } else {
            // Persona natural - usar nombres individuales
            $contactData['firstName'] = $company->firstName;
            $contactData['secondName'] = $company->secondName;
            $contactData['lastName'] = $company->lastName;
            $contactData['secondLastName'] = $company->secondLastName;
        }

        VntContacts::create($contactData);
    }

    /**
     * Actualizar contacto básico de una empresa
     * Actualiza el contacto existente con los nuevos datos de la empresa
     */
    public function updateContactForCompany(VntCompany $company, array $additionalData = []): void
    {
        // Obtener el primer contacto de la empresa (el contacto principal)
        $contact = $company->contacts()->first();
        
        if (!$contact) {
            // Si no existe contacto, crear uno nuevo
            $this->createContactsForCompany($company, $additionalData);
            return;
        }

        $contactData = [
            'email' => $company->billingEmail,
            'status' => $company->status,
            'business_phone' => $additionalData['business_phone'] ?? $contact->business_phone,
            'personal_phone' => $additionalData['personal_phone'] ?? $contact->personal_phone,
            'positionId' => $additionalData['positionId'] ?? $contact->positionId,
        ];

        // Actualizar nombres según el tipo de persona
        if ($company->typePerson === 'LEGAL_ENTITY') {
            // Persona jurídica - solo firstName con businessName
            $contactData['firstName'] = $company->businessName;
            $contactData['secondName'] = null;
            $contactData['lastName'] = null;
            $contactData['secondLastName'] = null;
        } else {
            // Persona natural - usar nombres individuales
            $contactData['firstName'] = $company->firstName;
            $contactData['secondName'] = $company->secondName;
            $contactData['lastName'] = $company->lastName;
            $contactData['secondLastName'] = $company->secondLastName;
        }

        $contact->update($contactData);
    }

    /**
     * Obtener contactos de una empresa con sus relaciones
     */
    public function getCompanyContacts(VntCompany $company): \Illuminate\Database\Eloquent\Collection
    {
        return $company->contacts()->with(['warehouse', 'position'])->get();
    }
}