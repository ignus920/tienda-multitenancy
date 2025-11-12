<?php

namespace App\Livewire\Tenant\VntCompany;

use Livewire\Component;
use App\Models\Tenant\Customer\VntCompany;
use App\Livewire\Tenant\VntCompany\Services\ContactService;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;

class ContactManagementModal extends Component
{
    // Modal state
    public $companyId;
    public $companyName = '';
    
    // Form state
    public $formMode = null; // null, 'create', 'edit'
    public $editingContactId = null;
    
    // Form data
    public $contactForm = [
        'firstName' => '',
        'secondName' => '',
        'lastName' => '',
        'secondLastName' => '',
        'email' => '',
        'business_phone' => '',
        'personal_phone' => '',
        'warehouseId' => '',
        'positionId' => '',
    ];
    
    // Data collections
    public $contacts = [];
    public $warehouses = [];
    public $positions = [];
    
    // Messages
    public $successMessage = '';
    public $errorMessage = '';
    
    protected $contactService;
    
    public function boot(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }
    
    public function mount($companyId)
    {
        $this->companyId = $companyId;
        $this->loadCompanyData();
        $this->loadWarehouses();
        $this->loadPositions();
    }
    
    public function render()
    {
        $this->ensureTenantConnection();
        $this->loadContacts();
        
        return view('livewire.tenant.vnt-company.contact-management-modal', [
            'contacts' => $this->contacts,
            'warehouses' => $this->warehouses,
            'positions' => $this->positions
        ]);
    }
    
    private function loadCompanyData()
    {
        $this->ensureTenantConnection();
        $company = VntCompany::findOrFail($this->companyId);
        $this->companyName = $company->businessName ?: trim($company->firstName . ' ' . $company->lastName);
    }
    
    /**
     * Cargar contactos de la empresa con eager loading
     * Requirements: 1.3, 6.5
     */
    public function loadContacts()
    {
        $this->ensureTenantConnection();
        $this->contacts = $this->contactService->getContactsByCompany($this->companyId);
    }
    
    /**
     * Cargar sucursales de la empresa filtrando por companyId
     * Requirements: 7.4
     */
    public function loadWarehouses()
    {
        $this->ensureTenantConnection();
        $this->warehouses = $this->contactService->getCompanyWarehouses($this->companyId);
    }
    
    /**
     * Cargar cargos disponibles
     * Requirements: 1.4
     */
    public function loadPositions()
    {
        $this->ensureTenantConnection();
        $this->positions = $this->contactService->getAvailablePositions();
    }
    
    /**
     * Cerrar modal y dispatch evento
     * Requirements: 1.3, 1.4
     */
    public function closeModal()
    {
        $this->dispatch('contact-modal-closed');
    }
    
    /**
     * Iniciar creación de nuevo contacto
     * Requirements: 2.1
     */
    public function startCreateContact()
    {
        $this->formMode = 'create';
        $this->editingContactId = null;
        $this->resetContactForm();
        $this->clearMessages();
    }
    
    /**
     * Cargar datos de contacto para edición
     * Requirements: 3.1
     */
    public function editContact($contactId)
    {
        try {
            $this->ensureTenantConnection();
            
            $contact = \App\Models\Tenant\Customer\VntContacts::with(['warehouse', 'position'])
                ->findOrFail($contactId);
            
            // Validar que el contacto pertenece a la empresa
            if ($contact->warehouse->companyId != $this->companyId) {
                $this->errorMessage = 'El contacto no pertenece a esta empresa';
                return;
            }
            
            $this->formMode = 'edit';
            $this->editingContactId = $contactId;
            
            // Cargar datos en el formulario
            $this->contactForm = [
                'firstName' => $contact->firstName,
                'secondName' => $contact->secondName ?? '',
                'lastName' => $contact->lastName,
                'secondLastName' => $contact->secondLastName ?? '',
                'email' => $contact->email ?? '',
                'business_phone' => $contact->business_phone ?? '',
                'personal_phone' => $contact->personal_phone ?? '',
                'warehouseId' => $contact->warehouseId,
                'positionId' => $contact->positionId,
            ];
            
            $this->clearMessages();
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al cargar el contacto: ' . $e->getMessage();
            Log::error('Error loading contact for edit', [
                'contactId' => $contactId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Guardar contacto (crear o actualizar)
     * Requirements: 2.5, 2.6, 2.7, 3.3, 3.4, 3.5, 3.6
     */
    public function saveContact()
    {
        try {
            $this->ensureTenantConnection();
            
            // Validar datos del formulario
            $this->validate($this->rules(), $this->messages());
            
            // Preparar datos para el servicio
            $data = array_merge($this->contactForm, [
                'companyId' => $this->companyId
            ]);
            
            if ($this->formMode === 'create') {
                // Crear nuevo contacto
                $this->contactService->createContact($data);
                $this->successMessage = 'Contacto creado exitosamente';
                
                Log::info('Contact created successfully', [
                    'companyId' => $this->companyId,
                    'contactData' => $data
                ]);
                
            } elseif ($this->formMode === 'edit') {
                // Actualizar contacto existente
                $this->contactService->updateContact($this->editingContactId, $data);
                $this->successMessage = 'Contacto actualizado exitosamente';
                
                Log::info('Contact updated successfully', [
                    'contactId' => $this->editingContactId,
                    'companyId' => $this->companyId,
                    'contactData' => $data
                ]);
            }
            
            // Limpiar formulario y recargar contactos
            $this->formMode = null;
            $this->editingContactId = null;
            $this->resetContactForm();
            $this->loadContacts();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-lanzar excepciones de validación para que Livewire las maneje
            throw $e;
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al guardar el contacto: ' . $e->getMessage();
            
            Log::error('Error saving contact', [
                'formMode' => $this->formMode,
                'contactId' => $this->editingContactId,
                'companyId' => $this->companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Eliminar contacto (soft delete)
     * Requirements: 4.1, 4.2, 4.3, 4.4, 4.5
     */
    public function deleteContact($contactId)
    {
        try {
            $this->ensureTenantConnection();
            
            // Validar que el contacto pertenece a la empresa
            $contact = \App\Models\Tenant\Customer\VntContacts::with('warehouse')
                ->findOrFail($contactId);
            
            if ($contact->warehouse->companyId != $this->companyId) {
                $this->errorMessage = 'El contacto no pertenece a esta empresa';
                return;
            }
            
            // Eliminar contacto (soft delete)
            $this->contactService->deleteContact($contactId);
            
            $this->successMessage = 'Contacto eliminado exitosamente';
            
            Log::info('Contact deleted successfully', [
                'contactId' => $contactId,
                'companyId' => $this->companyId
            ]);
            
            // Recargar contactos
            $this->loadContacts();
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al eliminar el contacto: ' . $e->getMessage();
            
            Log::error('Error deleting contact', [
                'contactId' => $contactId,
                'companyId' => $this->companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Cambiar estado de contacto (activo/inactivo)
     * Requirements: 5.1, 5.2, 5.3, 5.4, 5.5
     */
    public function toggleContactStatus($contactId)
    {
        try {
            $this->ensureTenantConnection();
            
            // Validar que el contacto pertenece a la empresa
            $contact = \App\Models\Tenant\Customer\VntContacts::with('warehouse')
                ->findOrFail($contactId);
            
            if ($contact->warehouse->companyId != $this->companyId) {
                $this->errorMessage = 'El contacto no pertenece a esta empresa';
                return;
            }
            
            // Toggle status
            $updatedContact = $this->contactService->toggleContactStatus($contactId);
            
            $statusText = $updatedContact->status === 1 ? 'activado' : 'desactivado';
            $this->successMessage = "Contacto {$statusText} exitosamente";
            
            Log::info('Contact status toggled successfully', [
                'contactId' => $contactId,
                'companyId' => $this->companyId,
                'newStatus' => $updatedContact->status
            ]);
            
            // Recargar contactos
            $this->loadContacts();
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al cambiar el estado del contacto: ' . $e->getMessage();
            
            Log::error('Error toggling contact status', [
                'contactId' => $contactId,
                'companyId' => $this->companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Cancelar formulario y cerrar
     * Requirements: 2.2, 3.2, 7.6
     */
    public function cancelForm()
    {
        $this->formMode = null;
        $this->editingContactId = null;
        $this->resetContactForm();
        $this->clearMessages();
        $this->resetValidation();
    }
    
    /**
     * Limpiar formulario de contacto
     * Requirements: 7.7
     */
    private function resetContactForm()
    {
        $this->contactForm = [
            'firstName' => '',
            'secondName' => '',
            'lastName' => '',
            'secondLastName' => '',
            'email' => '',
            'business_phone' => '',
            'personal_phone' => '',
            'warehouseId' => '',
            'positionId' => '',
        ];
    }
    
    /**
     * Limpiar mensajes de éxito y error
     */
    private function clearMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }
    
    /**
     * Reglas de validación del formulario
     */
    protected function rules()
    {
        return [
            'contactForm.firstName' => 'required|string|max:50',
            'contactForm.secondName' => 'nullable|string|max:50',
            'contactForm.lastName' => 'required|string|max:50',
            'contactForm.secondLastName' => 'nullable|string|max:50',
            'contactForm.email' => 'nullable|email|max:100',
            'contactForm.business_phone' => 'nullable|string|max:20',
            'contactForm.personal_phone' => 'nullable|string|max:20',
            'contactForm.warehouseId' => 'required|exists:vnt_warehouses,id',
            'contactForm.positionId' => 'required|exists:cfg_positions,id',
        ];
    }
    
    /**
     * Mensajes personalizados de validación
     */
    protected function messages()
    {
        return [
            'contactForm.firstName.required' => 'El primer nombre es obligatorio',
            'contactForm.firstName.max' => 'El primer nombre no puede exceder 50 caracteres',
            'contactForm.lastName.required' => 'El primer apellido es obligatorio',
            'contactForm.lastName.max' => 'El primer apellido no puede exceder 50 caracteres',
            'contactForm.email.email' => 'El email debe tener un formato válido',
            'contactForm.email.max' => 'El email no puede exceder 100 caracteres',
            'contactForm.warehouseId.required' => 'La sucursal es obligatoria',
            'contactForm.warehouseId.exists' => 'La sucursal seleccionada no es válida',
            'contactForm.positionId.required' => 'El cargo es obligatorio',
            'contactForm.positionId.exists' => 'El cargo seleccionado no es válido',
        ];
    }
    
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
